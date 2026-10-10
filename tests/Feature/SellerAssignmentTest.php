<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\SellerPhone;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * seller_id = quién vendió, dato independiente de created_by (quién
 * registró el pedido; para el bot, siempre la cuenta de integración).
 *
 * El bot lo resuelve desde seller_phone contra la tabla seller_phones; el
 * formulario web lo toma de un selector opcional. "Vendedor válido" tiene
 * una sola definición, User::scopeSellers(): activo y no integración.
 */
class SellerAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private City $city;

    private User $bot;

    protected function setUp(): void
    {
        parent::setUp();

        Stage::factory()->create(['order' => 1]);

        $this->product = Product::factory()->create([
            'active' => true,
            'base_price' => 300000,
            'shipping_price' => 20000,
        ]);

        $department = Department::factory()->create();
        $this->city = City::factory()->create(['department_id' => $department->id]);

        $this->bot = User::factory()->integration()->create();
    }

    private function actAsBot(): void
    {
        Sanctum::actingAs($this->bot, ['whatsapp:orders:create']);
    }

    private function botPayload(array $overrides = []): array
    {
        return array_merge([
            'idempotency_key' => 'wa-seller-base',
            'client_first_name' => 'Ana',
            'client_last_name' => 'Gómez',
            'client_phone' => '3001112233',
            'city_id' => $this->city->id,
            'department_id' => $this->city->department_id,
            'address' => 'Calle 1 #2-3',
            'product_id' => $this->product->id,
            'cart_color' => 'Gris',
        ], $overrides);
    }

    // ─── Bot ──────────────────────────────────────────────────

    public function test_a_known_seller_phone_sets_the_seller_without_touching_created_by(): void
    {
        $this->actAsBot();

        $seller = User::factory()->admin()->create(['name' => 'Jhon Alex']);
        SellerPhone::create(['phone' => '573172196131', 'user_id' => $seller->id]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-seller-ok',
            'seller_phone' => '+573172196131',
        ]));

        $response->assertCreated();

        $order = ProductionOrder::firstOrFail();
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame($this->bot->id, $order->created_by, 'created_by debe seguir siendo la cuenta de integración.');
    }

    public function test_the_phone_is_matched_regardless_of_format(): void
    {
        $this->actAsBot();

        $seller = User::factory()->director()->create();
        SellerPhone::create(['phone' => '573157298442', 'user_id' => $seller->id]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-seller-format',
            'seller_phone' => '+57 315 729 8442',
        ]));

        $response->assertCreated();
        $this->assertSame($seller->id, ProductionOrder::firstOrFail()->seller_id);
    }

    public function test_an_unknown_seller_phone_is_rejected_and_creates_nothing(): void
    {
        $this->actAsBot();

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-seller-unknown',
            'seller_phone' => '+573009998877',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('seller_phone');
        $this->assertDatabaseCount('production_orders', 0);
    }

    public function test_a_phone_of_an_inactive_user_is_rejected(): void
    {
        $this->actAsBot();

        $inactive = User::factory()->admin()->create(['active' => false]);
        SellerPhone::create(['phone' => '573012988541', 'user_id' => $inactive->id]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-seller-inactive',
            'seller_phone' => '+573012988541',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('seller_phone');
        $this->assertDatabaseCount('production_orders', 0);
    }

    public function test_a_phone_pointing_at_the_integration_account_is_rejected(): void
    {
        $this->actAsBot();

        SellerPhone::create(['phone' => '573001234567', 'user_id' => $this->bot->id]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-seller-bot',
            'seller_phone' => '+573001234567',
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('production_orders', 0);
    }

    /**
     * Hoy seller_phone es opcional porque n8n todavía no lo manda siempre.
     * Cuando lo haga, se aprieta a obligatorio (ver 15-integracion-whatsapp.md).
     */
    public function test_an_order_without_seller_phone_is_created_with_a_null_seller(): void
    {
        $this->actAsBot();

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-seller-absent',
        ]));

        $response->assertCreated();
        $this->assertNull(ProductionOrder::firstOrFail()->seller_id);
    }

    // ─── Formulario web ───────────────────────────────────────

    public function test_the_create_form_lists_only_valid_sellers(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Visible']);
        User::factory()->worker()->create(['name' => 'Worker Visible']);
        User::factory()->admin()->create(['name' => 'Inactivo Oculto', 'active' => false]);

        $response = $this->actingAs($admin)->get('/production-orders/create');

        $response->assertOk();
        $response->assertSee('Admin Visible', false);
        $response->assertSee('Worker Visible', false);
        $response->assertDontSee('Inactivo Oculto', false);
        $response->assertDontSee($this->bot->name, false);
    }

    public function test_the_configured_default_seller_is_preselected(): void
    {
        $admin = User::factory()->admin()->create();
        $seller = User::factory()->director()->create(['name' => 'Preseleccionado']);

        config(['business.default_seller_user_id' => $seller->id]);

        $response = $this->actingAs($admin)->get('/production-orders/create');

        $response->assertOk();
        $this->assertSame((string) $seller->id, $response->viewData('defaultSellerId'));
    }

    public function test_a_default_seller_that_is_not_a_valid_seller_is_ignored(): void
    {
        $admin = User::factory()->admin()->create();
        $inactive = User::factory()->admin()->create(['active' => false]);

        config(['business.default_seller_user_id' => $inactive->id]);

        $response = $this->actingAs($admin)->get('/production-orders/create');

        $response->assertOk();
        $this->assertNull($response->viewData('defaultSellerId'));
    }

    public function test_a_manual_order_stores_the_selected_seller(): void
    {
        $admin = User::factory()->admin()->create();
        $seller = User::factory()->director()->create();

        $response = $this->actingAs($admin)->post('/production-orders', [
            'client_first_name' => 'Luis',
            'client_last_name' => 'Pérez',
            'client_phone' => '3005554433',
            'client_address' => 'Calle 9 #8-7',
            'client_department' => $this->city->department_id,
            'client_city' => $this->city->id,
            'product_id' => $this->product->id,
            'color' => 'Gris',
            'price' => 300000,
            'due_date' => now()->addDays(15)->toDateString(),
            'seller_id' => $seller->id,
        ]);

        $response->assertRedirect();

        $order = ProductionOrder::firstOrFail();
        $this->assertSame($seller->id, $order->seller_id);
        $this->assertSame($admin->id, $order->created_by);
    }

    public function test_a_manual_order_without_seller_is_allowed(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/production-orders', [
            'client_first_name' => 'Luis',
            'client_last_name' => 'Pérez',
            'client_phone' => '3005554433',
            'client_address' => 'Calle 9 #8-7',
            'client_department' => $this->city->department_id,
            'client_city' => $this->city->id,
            'product_id' => $this->product->id,
            'color' => 'Gris',
            'price' => 300000,
            'due_date' => now()->addDays(15)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertNull(ProductionOrder::firstOrFail()->seller_id);
    }

    public function test_an_invalid_seller_id_is_rejected_by_the_web_form(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/production-orders', [
            'client_first_name' => 'Luis',
            'client_last_name' => 'Pérez',
            'client_phone' => '3005554433',
            'client_address' => 'Calle 9 #8-7',
            'client_department' => $this->city->department_id,
            'client_city' => $this->city->id,
            'product_id' => $this->product->id,
            'color' => 'Gris',
            'price' => 300000,
            'due_date' => now()->addDays(15)->toDateString(),
            'seller_id' => $this->bot->id, // cuenta de integración
        ]);

        $response->assertSessionHasErrors('seller_id');
        $this->assertDatabaseCount('production_orders', 0);
    }

    // ─── Comando de vinculación ───────────────────────────────

    public function test_the_command_links_a_phone_and_is_idempotent(): void
    {
        $seller = User::factory()->admin()->create();

        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $seller->id, 'phone' => '+573172196131'])
            ->assertSuccessful();
        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $seller->id, 'phone' => '+57 317 219 6131'])
            ->assertSuccessful();

        $this->assertSame(1, SellerPhone::where('phone', '573172196131')->count());
        $this->assertDatabaseHas('seller_phones', ['phone' => '573172196131', 'user_id' => $seller->id]);
    }

    public function test_the_command_links_several_phones_to_the_same_seller(): void
    {
        $seller = User::factory()->admin()->create();

        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $seller->id, 'phone' => '+573172196131'])
            ->assertSuccessful();
        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $seller->id, 'phone' => '+573157298442'])
            ->assertSuccessful();

        $this->assertSame(2, SellerPhone::where('user_id', $seller->id)->count());
    }

    public function test_the_command_refuses_to_move_a_phone_between_sellers_without_force(): void
    {
        $first = User::factory()->admin()->create();
        $second = User::factory()->director()->create();

        SellerPhone::create(['phone' => '573172196131', 'user_id' => $first->id]);

        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $second->id, 'phone' => '+573172196131'])
            ->assertFailed();

        $this->assertDatabaseHas('seller_phones', ['phone' => '573172196131', 'user_id' => $first->id]);
    }

    public function test_the_command_moves_a_phone_with_force(): void
    {
        $first = User::factory()->admin()->create();
        $second = User::factory()->director()->create();

        SellerPhone::create(['phone' => '573172196131', 'user_id' => $first->id]);

        $this->artisan('whatsapp:link-seller-phone', [
            'user_id' => $second->id,
            'phone' => '+573172196131',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('seller_phones', ['phone' => '573172196131', 'user_id' => $second->id]);
        $this->assertSame(1, SellerPhone::count());
    }

    public function test_the_command_rejects_a_user_that_is_not_a_valid_seller(): void
    {
        $inactive = User::factory()->admin()->create(['active' => false]);

        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $inactive->id, 'phone' => '+573172196131'])
            ->assertFailed();
        $this->artisan('whatsapp:link-seller-phone', ['user_id' => $this->bot->id, 'phone' => '+573172196131'])
            ->assertFailed();

        $this->assertSame(0, SellerPhone::count());
    }
}
