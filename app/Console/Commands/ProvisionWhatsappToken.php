<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Reusa el usuario role=integration ya existente (n8n@autocarterascali.com,
 * confirmado en auditoría 2026-09-27 — nunca se usó su token anterior,
 * "n8n-integration", que tenía abilities demasiado amplias: clients:read,
 * production-orders:read, payments:read, stages:read). Este comando lo
 * revoca y emite uno nuevo, acotado solo a lo que el bot necesita.
 */
class ProvisionWhatsappToken extends Command
{
    protected $signature = 'whatsapp:provision-token {--force : No pedir confirmación antes de revocar un token existente}';

    protected $description = 'Emite (o reemplaza) el token del bot de WhatsApp con abilities mínimas';

    private const ABILITIES = ['whatsapp:catalog:read', 'whatsapp:orders:create'];

    public function handle(): int
    {
        $user = User::where('role', 'integration')->first();

        if (! $user) {
            $this->error("No existe ningún usuario role=integration. Créalo primero (no es responsabilidad de este comando decidir sus datos).");
            return self::FAILURE;
        }

        $this->info("Usuario de integración: {$user->email} (id={$user->id})");

        // Requisito crítico: EnsureUserIsActive (Fase 2 de la auditoría de
        // seguridad) bloquea con 403 cualquier request de un usuario
        // inactivo — sin esto el bot no podría hacer ni la primera llamada.
        if (! $user->active) {
            $user->forceFill(['active' => true])->save();
            $this->warn('El usuario estaba inactivo -- se forzó active=true.');
        } else {
            $this->info('active=true confirmado.');
        }

        $oldToken = $user->tokens()->where('name', 'n8n-integration')->first();
        if ($oldToken) {
            $lastUsed = $oldToken->last_used_at?->toDateTimeString() ?? 'nunca';
            $confirmed = $this->option('force') || $this->confirm(
                "Existe el token 'n8n-integration' (creado {$oldToken->created_at}, último uso: {$lastUsed}) con abilities amplias. ¿Revocarlo ahora?"
            );
            if (! $confirmed) {
                $this->info('Cancelado — no se tocó ningún token.');
                return self::SUCCESS;
            }
            $oldToken->delete();
            $this->info("Token 'n8n-integration' revocado.");
        }

        $existingBotToken = $user->tokens()->where('name', 'whatsapp-bot')->first();
        if ($existingBotToken) {
            $existingBotToken->delete();
            $this->warn("Ya existía un token 'whatsapp-bot' -- se revocó antes de emitir el nuevo.");
        }

        $token = $user->createToken('whatsapp-bot', self::ABILITIES);

        $this->newLine();
        $this->info('Token nuevo (guárdalo ahora — no se puede volver a mostrar):');
        $this->line($token->plainTextToken);
        $this->newLine();
        $this->info('Abilities: ' . implode(', ', self::ABILITIES));

        return self::SUCCESS;
    }
}
