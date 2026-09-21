<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Worker y Director comparten esta vista y el mismo filtro por habilidad
 * (user_skills) — swipe-to-advance, con cliente/ciudad visibles pero sin
 * nada financiero (sección 21). orders/operative.blade.php ya no existe.
 */
class MyTasksViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // El hardcode current_stage_id == 8 ya se corrigió (ver
        // AdvanceStageEnviadoGuardTest) — se mantiene este setval solo como
        // margen extra para que los IDs de etapas de estos tests no choquen
        // con nada inesperado en la secuencia de la BD de test.
        \Illuminate\Support\Facades\DB::statement("SELECT setval('stages_id_seq', 1000, false)");
    }

    public function test_worker_sees_my_tasks_view_with_client_and_city_but_no_financial_data(): void
    {
        $worker = User::factory()->worker()->create();
        $stage = Stage::factory()->create();
        $worker->skills()->attach($stage->id);

        $order = ProductionOrder::factory()->create([
            'status' => 'pending',
            'current_stage_id' => $stage->id,
        ]);

        $response = $this->actingAs($worker)->get('/orders');

        $response->assertOk();
        $response->assertViewIs('orders.my-tasks');
        $response->assertSee('Mis tareas');
        $response->assertSee($order->product->name);
        $response->assertSee($order->client->full_name);
        $response->assertSee($order->client->city->name);
        $response->assertDontSee($order->client->phone);
        $response->assertDontSee('Debe $');
    }

    public function test_director_sees_the_same_my_tasks_view_filtered_by_own_skills(): void
    {
        $director = User::factory()->director()->create();
        $stage = Stage::factory()->create();
        $director->skills()->attach($stage->id);

        $order = ProductionOrder::factory()->create(['current_stage_id' => $stage->id, 'status' => 'pending']);

        $response = $this->actingAs($director)->get('/orders');

        $response->assertOk();
        $response->assertViewIs('orders.my-tasks');
        $response->assertSee($order->client->full_name);
        $response->assertSee($order->client->city->name);
    }

    public function test_worker_only_sees_orders_in_workable_stages(): void
    {
        $worker = User::factory()->worker()->create();
        $workableStage = Stage::factory()->create();
        $otherStage = Stage::factory()->create();
        $worker->skills()->attach($workableStage->id);

        $visible = ProductionOrder::factory()->create(['current_stage_id' => $workableStage->id, 'status' => 'pending']);
        $hidden = ProductionOrder::factory()->create(['current_stage_id' => $otherStage->id, 'status' => 'pending']);

        $response = $this->actingAs($worker)->get('/orders');

        $response->assertSee($visible->product->name);
        $response->assertDontSee($hidden->product->name);
    }

    public function test_director_only_sees_orders_in_their_own_workable_stages(): void
    {
        $director = User::factory()->director()->create();
        $workableStage = Stage::factory()->create();
        $otherStage = Stage::factory()->create();
        $director->skills()->attach($workableStage->id);

        $visible = ProductionOrder::factory()->create(['current_stage_id' => $workableStage->id, 'status' => 'pending']);
        $hidden = ProductionOrder::factory()->create(['current_stage_id' => $otherStage->id, 'status' => 'pending']);

        $response = $this->actingAs($director)->get('/orders');

        $response->assertSee($visible->product->name);
        $response->assertDontSee($hidden->product->name);
    }

    public function test_worker_can_advance_stage_via_swipe_endpoint_when_skilled(): void
    {
        $worker = User::factory()->worker()->create();
        // order fuera del rango 1-10 de StageFactory: ProductionOrderFactory
        // crea una etapa "fantasma" vía su Stage::factory() por defecto
        // incluso cuando current_stage_id se sobrescribe (Laravel evalua
        // toda la definicion antes de aplicar overrides) — con order bajo,
        // esa fantasma podia empatar con stageA/stageB y volver no
        // determinista el orderBy('order')->first() de advanceStage().
        $stageA = Stage::factory()->create(['order' => 1000]);
        $stageB = Stage::factory()->create(['order' => 1001]);
        $worker->skills()->attach($stageA->id);

        $order = ProductionOrder::factory()->create(['current_stage_id' => $stageA->id, 'status' => 'pending']);
        $order->orderStages()->create(['stage_id' => $stageA->id, 'started_at' => now()]);

        $this->actingAs($worker)
            ->post("/production-orders/{$order->id}/advance-stage")
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertEquals($stageB->id, $order->fresh()->current_stage_id);
    }

    public function test_worker_cannot_advance_stage_without_the_skill(): void
    {
        $worker = User::factory()->worker()->create();
        $stageA = Stage::factory()->create(['order' => 1000]);
        Stage::factory()->create(['order' => 1001]);
        // El worker NO tiene la habilidad de $stageA

        $order = ProductionOrder::factory()->create(['current_stage_id' => $stageA->id, 'status' => 'pending']);
        $order->orderStages()->create(['stage_id' => $stageA->id, 'started_at' => now()]);

        $this->actingAs($worker)->post("/production-orders/{$order->id}/advance-stage");

        $this->assertEquals($stageA->id, $order->fresh()->current_stage_id);
    }

    public function test_director_can_advance_stage_via_swipe_endpoint_when_skilled(): void
    {
        $director = User::factory()->director()->create();
        $stageA = Stage::factory()->create(['order' => 1000]);
        $stageB = Stage::factory()->create(['order' => 1001]);
        $director->skills()->attach($stageA->id);

        $order = ProductionOrder::factory()->create(['current_stage_id' => $stageA->id, 'status' => 'pending']);
        $order->orderStages()->create(['stage_id' => $stageA->id, 'started_at' => now()]);

        $this->actingAs($director)
            ->post("/production-orders/{$order->id}/advance-stage")
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertEquals($stageB->id, $order->fresh()->current_stage_id);
    }

    public function test_swipe_shows_the_real_next_stage_name_instead_of_a_fixed_label(): void
    {
        $worker = User::factory()->worker()->create();
        $stageA = Stage::factory()->create(['order' => 1000, 'name' => 'Etapa Actual']);
        $stageB = Stage::factory()->create(['order' => 1001, 'name' => 'Etapa Siguiente']);
        $worker->skills()->attach($stageA->id);

        $order = ProductionOrder::factory()->create(['current_stage_id' => $stageA->id, 'status' => 'pending']);

        $response = $this->actingAs($worker)->get('/orders');

        $response->assertOk();
        $this->assertEquals($stageB->id, $order->nextStage()?->id);
        $response->assertSee('→ Etapa Siguiente');
        $response->assertDontSee('>Terminada<', false);
    }

    public function test_swipe_falls_back_to_finalizado_when_there_is_no_next_active_stage(): void
    {
        $worker = User::factory()->worker()->create();
        $stage = Stage::factory()->create(['order' => 1000, 'name' => 'Última Etapa']);
        $worker->skills()->attach($stage->id);

        $order = ProductionOrder::factory()->create(['current_stage_id' => $stage->id, 'status' => 'pending']);

        $this->assertNull($order->nextStage());

        $response = $this->actingAs($worker)->get('/orders');

        $response->assertOk();
        $response->assertSee('→ Finalizado');
    }
}
