<?php

namespace Tests\Feature;

use App\Mail\OrderCreatedMail;
use App\Models\City;
use App\Models\Client;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * El correo de "orden creada" vivía solo en el controller web, así que un
 * pedido creado por el bot de WhatsApp nunca lo disparaba aunque el cliente
 * tuviera correo registrado. Ahora el único punto de envío es
 * ProductionOrderService::notifyCreated(), llamado por los dos canales
 * después de registrar el anticipo y después del commit.
 *
 * Las aserciones son assertQueued/assertNothingQueued, no assertSent: como
 * OrderCreatedMail implementa ShouldQueue, Mailer::sendMailable()
 * (vendor/laravel/framework/src/Illuminate/Mail/Mailer.php:349-354) deriva
 * el envío a ->queue() aunque MailService llame a ->send(). Con assertSent
 * estas pruebas pasarían en falso: assertNothingSent() da verde incluso con
 * un correo encolado.
 */
class OrderCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        Stage::factory()->create(['order' => 1]);

        // Producto 300.000 + envío 20.000 = 320.000 de total a pagar.
        $this->product = Product::factory()->create([
            'active' => true,
            'base_price' => 300000,
            'shipping_price' => 20000,
        ]);

        $department = Department::factory()->create();
        $this->city = City::factory()->create(['department_id' => $department->id]);
    }

    private function actAsBot(): void
    {
        Sanctum::actingAs(
            User::factory()->integration()->create(),
            ['whatsapp:orders:create'],
        );
    }

    private function botPayload(array $overrides = []): array
    {
        return array_merge([
            'idempotency_key' => 'wa-notify-base',
            'client_first_name' => 'Ana',
            'client_last_name' => 'Gómez',
            'client_phone' => '3001112233',
            'client_email' => 'ana@example.com',
            'city_id' => $this->city->id,
            'department_id' => $this->city->department_id,
            'address' => 'Calle 1 #2-3',
            'product_id' => $this->product->id,
            'cart_color' => 'Gris',
        ], $overrides);
    }

    public function test_bot_order_sends_the_creation_email_with_the_advance_already_reflected(): void
    {
        Mail::fake();
        $this->actAsBot();

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-notify-advance',
            'advance_amount' => 100000,
        ]));

        $response->assertCreated();

        // La plantilla muestra total pagado y saldo pendiente: si el correo
        // saliera antes de registrar el anticipo (o dentro de create()),
        // mostraría 0 pagado y 320.000 de saldo.
        Mail::assertQueued(OrderCreatedMail::class, function (OrderCreatedMail $mail) {
            $body = $mail->render();

            return str_contains($body, '100.000') && str_contains($body, '220.000');
        });
    }

    public function test_an_idempotent_replay_does_not_resend_the_email(): void
    {
        Mail::fake();
        $this->actAsBot();

        $payload = $this->botPayload(['idempotency_key' => 'wa-notify-replay']);

        $this->postJson('/api/v1/whatsapp/orders', $payload)->assertCreated();

        $queuedAfterFirst = Mail::queued(OrderCreatedMail::class)->count();
        $this->assertGreaterThan(0, $queuedAfterFirst);

        $replay = $this->postJson('/api/v1/whatsapp/orders', $payload);
        $replay->assertOk();
        $replay->assertJsonPath('idempotent_replay', true);

        $this->assertSame(
            $queuedAfterFirst,
            Mail::queued(OrderCreatedMail::class)->count(),
            'Un replay idempotente no debe reenviar el correo.',
        );
    }

    /**
     * El correo no puede salir antes del commit: si el anticipo revienta
     * dentro de la transacción, no queda orden ni debe salir notificación.
     */
    public function test_a_failed_advance_payment_rolls_back_and_sends_no_email(): void
    {
        Mail::fake();
        $this->actAsBot();

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-notify-rollback',
            'advance_amount' => 500000, // supera los 320.000 de saldo total
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('production_orders', 0);
        Mail::assertNothingQueued();
    }

    /**
     * Regla confirmada por el usuario: se envía si el cliente tiene correo
     * guardado al crear la orden — no depende de que el mensaje de WhatsApp
     * haya traído client_email esa vez.
     */
    public function test_email_goes_to_a_preexisting_client_even_if_the_payload_omits_it(): void
    {
        Mail::fake();
        $this->actAsBot();

        Client::factory()->create([
            'phone' => '3009998877',
            'email' => 'guardado@example.com',
        ]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-notify-existing',
            'client_phone' => '3009998877',
            'client_email' => null,
        ]));

        $response->assertCreated();
        Mail::assertQueued(
            OrderCreatedMail::class,
            fn (OrderCreatedMail $mail) => $mail->hasTo('guardado@example.com'),
        );
    }

    public function test_manual_order_still_sends_the_creation_email_with_its_advance(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/production-orders', [
            'client_first_name' => 'Luis',
            'client_last_name' => 'Pérez',
            'client_phone' => '3005554433',
            'client_email' => 'luis@example.com',
            'client_address' => 'Calle 9 #8-7',
            'client_department' => $this->city->department_id,
            'client_city' => $this->city->id,
            'product_id' => $this->product->id,
            'color' => 'Gris',
            'price' => 300000,
            'due_date' => now()->addDays(15)->toDateString(),
            'advance_payment' => 50000,
        ]);

        $response->assertRedirect();

        $order = ProductionOrder::firstOrFail();
        $this->assertNotNull($order->created_notified_at);
        $this->assertDatabaseHas('payments', [
            'production_order_id' => $order->id,
            'amount' => 50000,
            'type' => 'advance',
        ]);

        Mail::assertQueued(
            OrderCreatedMail::class,
            fn (OrderCreatedMail $mail) => str_contains($mail->render(), '50.000'),
        );
    }
}
