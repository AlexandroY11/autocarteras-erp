<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_reports_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/reports')->assertOk();
    }

    public function test_worker_gets_forbidden_on_reports_index(): void
    {
        $worker = User::factory()->worker()->create();

        $this->actingAs($worker)->get('/reports')->assertForbidden();
    }

    public function test_director_gets_forbidden_on_reports_index(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->get('/reports')->assertForbidden();
    }

    public function test_guest_is_redirected_from_reports_index(): void
    {
        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_admin_can_download_financial_report(): void
    {
        $admin = User::factory()->admin()->create();
        ProductionOrder::factory()->create();

        $response = $this->actingAs($admin)->get('/reports/financial?date_field=created_at');

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    public function test_financial_report_requires_a_date_field(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/reports/financial');

        $response->assertSessionHasErrors('date_field');
    }

    public function test_financial_report_rejects_invalid_date_field(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/reports/financial?date_field=not_a_real_field');

        $response->assertSessionHasErrors('date_field');
    }

    public function test_worker_cannot_download_financial_report(): void
    {
        $worker = User::factory()->worker()->create();

        $this->actingAs($worker)
            ->get('/reports/financial?date_field=created_at')
            ->assertForbidden();
    }

    public function test_admin_can_download_operational_report(): void
    {
        $admin = User::factory()->admin()->create();
        ProductionOrder::factory()->create();

        $response = $this->actingAs($admin)->get('/reports/operational');

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    public function test_worker_cannot_download_operational_report(): void
    {
        $worker = User::factory()->worker()->create();

        $this->actingAs($worker)->get('/reports/operational')->assertForbidden();
    }

    public function test_admin_can_view_production_list_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        ProductionOrder::factory()->create(['status' => 'pending', 'dispatch_status' => null]);

        $response = $this->actingAs($admin)->get('/reports/print-production-list');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_worker_cannot_view_production_list_pdf(): void
    {
        $worker = User::factory()->worker()->create();

        $this->actingAs($worker)->get('/reports/print-production-list')->assertForbidden();
    }

    /**
     * dispatch_status es NULL hasta que el pedido llega a "pending_dispatch"
     * — un whereNotIn() ingenuo excluye esas filas (NULL NOT IN (...) no es
     * TRUE en SQL). Este test fija ese comportamiento: un pedido en
     * producción con dispatch_status NULL debe seguir apareciendo.
     */
    public function test_production_list_query_includes_null_dispatch_status_and_excludes_dispatched(): void
    {
        $stillInShop = ProductionOrder::factory()->create([
            'status' => 'pending',
            'dispatch_status' => null,
        ]);
        $alreadyDispatched = ProductionOrder::factory()->create([
            'status' => 'done',
            'current_stage_id' => null,
            'dispatch_status' => 'dispatched',
        ]);
        $done = ProductionOrder::factory()->done()->create();

        $ids = ProductionOrder::whereNotIn('status', ['done', 'cancelled'])
            ->where(function ($query) {
                $query->whereNotIn('dispatch_status', ['dispatched', 'sent', 'delivered', 'returned'])
                    ->orWhereNull('dispatch_status');
            })
            ->pluck('id');

        $this->assertTrue($ids->contains($stillInShop->id));
        $this->assertFalse($ids->contains($alreadyDispatched->id));
        $this->assertFalse($ids->contains($done->id));
    }
}
