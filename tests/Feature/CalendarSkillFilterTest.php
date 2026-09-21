<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * calendar() y dayDetail() no filtraban por habilidad — cualquier rol veía
 * toda la operación. Worker/Director ahora ven solo lo que pueden trabajar,
 * igual que "Mis tareas"; Admin sigue viendo todo.
 */
class CalendarSkillFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_only_sees_workable_orders_in_calendar_month_view(): void
    {
        $worker = User::factory()->worker()->create();
        $workableStage = Stage::factory()->create();
        $otherStage = Stage::factory()->create();
        $worker->skills()->attach($workableStage->id);

        $dueDate = now()->addDays(5)->format('Y-m-d');
        $visible = ProductionOrder::factory()->create(['current_stage_id' => $workableStage->id, 'due_date' => $dueDate]);
        $hidden = ProductionOrder::factory()->create(['current_stage_id' => $otherStage->id, 'due_date' => $dueDate]);

        $response = $this->actingAs($worker)->get('/production-orders/calendar?month=' . now()->addDays(5)->month . '&year=' . now()->addDays(5)->year);

        $response->assertOk();
        $response->assertSee($visible->product->name);
        $response->assertDontSee($hidden->product->name);
    }

    public function test_worker_only_sees_workable_orders_in_day_detail(): void
    {
        $worker = User::factory()->worker()->create();
        $workableStage = Stage::factory()->create();
        $otherStage = Stage::factory()->create();
        $worker->skills()->attach($workableStage->id);

        $dueDate = now()->addDays(5)->format('Y-m-d');
        $visible = ProductionOrder::factory()->create(['current_stage_id' => $workableStage->id, 'due_date' => $dueDate]);
        $hidden = ProductionOrder::factory()->create(['current_stage_id' => $otherStage->id, 'due_date' => $dueDate]);

        $response = $this->actingAs($worker)->get("/production-orders/calendar/{$dueDate}");

        $response->assertOk();
        $response->assertSee($visible->product->name);
        $response->assertDontSee($hidden->product->name);
    }

    public function test_admin_still_sees_all_orders_in_day_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $stageA = Stage::factory()->create();
        $stageB = Stage::factory()->create();

        $dueDate = now()->addDays(5)->format('Y-m-d');
        $orderA = ProductionOrder::factory()->create(['current_stage_id' => $stageA->id, 'due_date' => $dueDate]);
        $orderB = ProductionOrder::factory()->create(['current_stage_id' => $stageB->id, 'due_date' => $dueDate]);

        $response = $this->actingAs($admin)->get("/production-orders/calendar/{$dueDate}");

        $response->assertOk();
        $response->assertSee($orderA->product->name);
        $response->assertSee($orderB->product->name);
    }

    public function test_day_detail_uses_the_shared_task_card_with_fecha_compromiso(): void
    {
        $admin = User::factory()->admin()->create();
        $stage = Stage::factory()->create();
        $dueDate = now()->addDays(5)->format('Y-m-d');
        $order = ProductionOrder::factory()->create(['current_stage_id' => $stage->id, 'due_date' => $dueDate]);

        $response = $this->actingAs($admin)->get("/production-orders/calendar/{$dueDate}");

        $response->assertOk();
        $response->assertSee('Fecha compromiso');
        $response->assertSee($order->client->full_name);
    }

    public function test_admin_sees_price_and_balance_in_day_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $stage = Stage::factory()->create();
        $dueDate = now()->addDays(5)->format('Y-m-d');
        $order = ProductionOrder::factory()->create(['current_stage_id' => $stage->id, 'due_date' => $dueDate]);

        $response = $this->actingAs($admin)->get("/production-orders/calendar/{$dueDate}");

        $response->assertOk();
        $response->assertSee('Debe $' . number_format($order->price, 0, ',', '.'), false);
    }

    public function test_worker_does_not_see_price_or_balance_in_day_detail(): void
    {
        $worker = User::factory()->worker()->create();
        $stage = Stage::factory()->create();
        $worker->skills()->attach($stage->id);
        $dueDate = now()->addDays(5)->format('Y-m-d');
        ProductionOrder::factory()->create(['current_stage_id' => $stage->id, 'due_date' => $dueDate]);

        $response = $this->actingAs($worker)->get("/production-orders/calendar/{$dueDate}");

        $response->assertOk();
        $response->assertDontSee('Debe $');
        $response->assertDontSee('Pagado');
    }
}
