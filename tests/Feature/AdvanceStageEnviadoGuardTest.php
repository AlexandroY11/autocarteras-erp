<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * ProductionOrderController::advanceStage() (y orders/show.blade.php) tenían
 * un current_stage_id == 8 hardcodeado asumiendo que ese ID siempre es la
 * etapa "Enviado". El ID numérico depende de la secuencia de cada BD — en
 * la BD de test, una etapa totalmente distinta terminó con ID 8 por
 * casualidad y fue bloqueada como si fuera "Enviado" sin serlo. El fix
 * busca la etapa por nombre (Stage::enviadoId()); estos tests fijan el
 * comportamiento correcto independientemente del ID numérico real.
 */
class AdvanceStageEnviadoGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_in_a_different_stage_that_happens_to_share_the_old_hardcoded_id_can_still_advance(): void
    {
        // Forzamos que la próxima etapa creada reciba id=8 — el mismo
        // número que estaba hardcodeado — pero con un nombre distinto a
        // "Enviado". Este es exactamente el escenario que rompió el hardcode.
        DB::statement("SELECT setval('stages_id_seq', 7, true)");
        $impostorStage = Stage::factory()->create(['name' => 'Etapa Cualquiera', 'order' => 1]);
        $this->assertEquals(8, $impostorStage->id, 'la etapa de prueba debe caer exactamente en id=8 para reproducir el bug');

        $nextStage = Stage::factory()->create(['order' => 2]);

        $admin = User::factory()->admin()->create();
        $order = ProductionOrder::factory()->create([
            'current_stage_id' => $impostorStage->id,
            'status' => 'pending',
        ]);
        $order->orderStages()->create(['stage_id' => $impostorStage->id, 'started_at' => now()]);

        $this->actingAs($admin)
            ->post("/production-orders/{$order->id}/advance-stage")
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertEquals($nextStage->id, $order->fresh()->current_stage_id);
    }

    public function test_order_actually_in_enviado_stage_is_blocked_regardless_of_its_numeric_id(): void
    {
        // "Enviado" nace con cualquier ID (no forzamos ninguno) — el bloqueo
        // debe activarse igual, porque ahora se busca por nombre.
        $enviado = Stage::factory()->create(['name' => 'Enviado', 'active' => false, 'order' => 5]);

        $admin = User::factory()->admin()->create();
        $order = ProductionOrder::factory()->create([
            'current_stage_id' => $enviado->id,
            'status' => 'pending',
        ]);
        $order->orderStages()->create(['stage_id' => $enviado->id, 'started_at' => now()]);

        $response = $this->actingAs($admin)->post("/production-orders/{$order->id}/advance-stage");

        $response->assertSessionHasErrors('error');
        $this->assertEquals(
            'La orden ya se encuentra en la etapa Enviado.',
            session('errors')->first('error')
        );
        $this->assertEquals($enviado->id, $order->fresh()->current_stage_id);
    }
}
