<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LaravelWebauthn\WebauthnAuthenticatable; 

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use WebauthnAuthenticatable;

    /**
     * Único mapeo rol→español — cualquier vista que muestre el rol en texto
     * debe usar role_label, no traducir por su cuenta (integration no
     * aparece aquí a propósito: es una cuenta técnica, no se muestra/asigna
     * desde el panel humano).
     */
    public const ROLE_LABELS = [
        'admin' => 'Administrador',
        'director' => 'Director',
        'worker' => 'Trabajador',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    // Relaciones
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Stage::class, 'user_skills');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isWorker(): bool
    {
        return $this->role === 'worker';
    }

    public function isDirector(): bool
    {
        return $this->role === 'director';
    }

    // Helpers de rol
    public function isOperative(): bool
    {
        return in_array($this->role, ['director', 'worker']);
    }

    // ¿Puede avanzar esta etapa?
    public function canAdvanceStage(int $stageId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->skills()->where('stage_id', $stageId)->exists();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }
}
