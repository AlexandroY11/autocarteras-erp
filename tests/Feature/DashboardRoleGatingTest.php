<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_financial_widgets(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Ingresos del Mes');
        $response->assertSee('Cartera Total');
    }

    public function test_worker_does_not_see_financial_widgets(): void
    {
        $worker = User::factory()->worker()->create();

        $response = $this->actingAs($worker)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Ingresos del Mes');
        $response->assertDontSee('Cartera Total');
    }

    public function test_director_does_not_see_financial_widgets(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Ingresos del Mes');
        $response->assertDontSee('Cartera Total');
    }

    public function test_worker_with_skill_sees_workable_orders_section(): void
    {
        $worker = User::factory()->worker()->create();
        $stage = Stage::factory()->create();
        $worker->skills()->attach($stage->id);

        ProductionOrder::factory()->create(['current_stage_id' => $stage->id, 'status' => 'pending']);

        $response = $this->actingAs($worker)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('etapas que puedes trabajar');
    }

    public function test_worker_without_skills_does_not_see_workable_orders_section(): void
    {
        $worker = User::factory()->worker()->create();

        $response = $this->actingAs($worker)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('etapas que puedes trabajar');
    }
}
