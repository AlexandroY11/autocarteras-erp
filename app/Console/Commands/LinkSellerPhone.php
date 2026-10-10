<?php

namespace App\Console\Commands;

use App\Models\SellerPhone;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Vincula un teléfono de WhatsApp (el remitente en Chatwoot) con el usuario
 * que vende desde ese número, para que el bot pueda resolver seller_id.
 *
 * Los datos van por argumento y no hardcodeados acá a propósito: los ids y
 * los números son datos de producción, no pertenecen al repositorio. Mismo
 * criterio que whatsapp:provision-token, que tampoco decide los datos del
 * usuario que usa.
 */
class LinkSellerPhone extends Command
{
    protected $signature = 'whatsapp:link-seller-phone
                            {user_id : Id del usuario vendedor}
                            {phone : Teléfono del remitente (cualquier formato: +57..., 57..., con espacios)}
                            {--force : Reasignar el teléfono aunque ya pertenezca a otro vendedor}';

    protected $description = 'Vincula un teléfono de WhatsApp con el vendedor que escribe desde él';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $user = User::query()->sellers()->find($userId);

        if (! $user) {
            $this->error("No hay un vendedor válido con id={$userId}. Debe existir, estar activo y no ser la cuenta de integración.");

            return self::FAILURE;
        }

        $phone = SellerPhone::normalize($this->argument('phone'));

        if ($phone === '') {
            $this->error('El teléfono no tiene ningún dígito.');

            return self::FAILURE;
        }

        $existing = SellerPhone::where('phone', $phone)->first();

        if ($existing && $existing->user_id !== $user->id) {
            $owner = User::withTrashed()->find($existing->user_id);
            $ownerName = $owner?->name ?? "id={$existing->user_id}";

            if (! $this->option('force')) {
                $this->error("El teléfono {$phone} ya está vinculado a {$ownerName}. Usa --force para reasignarlo.");

                return self::FAILURE;
            }

            $this->warn("El teléfono {$phone} estaba vinculado a {$ownerName} -- se reasigna.");
        }

        SellerPhone::updateOrCreate(
            ['phone' => $phone],
            ['user_id' => $user->id],
        );

        $this->info("Vinculado: {$phone} -> {$user->name} (id={$user->id}).");

        $this->newLine();
        $this->info('Mapeo actual:');
        $this->table(
            ['Teléfono', 'Vendedor', 'User id'],
            SellerPhone::with('user')->orderBy('user_id')->orderBy('phone')->get()
                ->map(fn (SellerPhone $p) => [$p->phone, $p->user?->name ?? '(sin usuario)', $p->user_id])
                ->all(),
        );

        return self::SUCCESS;
    }
}
