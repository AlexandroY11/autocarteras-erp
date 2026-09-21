<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use App\Models\WebauthnKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba de humo de la infraestructura de tests (Etapa 6, punto 0): confirma
 * que las factories de los modelos de negocio se pueden crear encadenadas
 * contra Postgres real, incluyendo la derivación de department_id a partir
 * del city_id generado en ClientFactory.
 */
class FactorySmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_factory_derives_matching_department_from_city(): void
    {
        $client = Client::factory()->create();

        $this->assertNotNull($client->city_id);
        $this->assertNotNull($client->department_id);
        $this->assertEquals($client->city->department_id, $client->department_id);
    }

    public function test_production_order_factory_creates_full_dependency_chain(): void
    {
        $order = ProductionOrder::factory()->create();

        $this->assertInstanceOf(Client::class, $order->client);
        $this->assertInstanceOf(Product::class, $order->product);
        $this->assertInstanceOf(Stage::class, $order->currentStage);
        $this->assertInstanceOf(User::class, $order->createdBy);
    }

    public function test_payment_factory_attaches_to_a_production_order(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(ProductionOrder::class, $payment->productionOrder);
    }

    public function test_stage_factory_states(): void
    {
        $autoComplete = Stage::factory()->autoComplete()->create();
        $inactive = Stage::factory()->inactive()->create();

        $this->assertTrue($autoComplete->auto_complete);
        $this->assertFalse($inactive->active);
    }

    public function test_production_order_factory_states(): void
    {
        $done = ProductionOrder::factory()->done()->create();
        $cancelled = ProductionOrder::factory()->cancelled()->create();
        $overdue = ProductionOrder::factory()->overdue()->create();

        $this->assertEquals('done', $done->status);
        $this->assertNull($done->current_stage_id);
        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertTrue($overdue->due_date->isPast());
    }

    public function test_webauthn_key_factory_creates_valid_key(): void
    {
        $key = WebauthnKey::factory()->create();

        $this->assertInstanceOf(User::class, User::find($key->user_id));
        $this->assertNotEmpty($key->credentialId);
    }
}
