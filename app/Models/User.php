<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'dni', 'email', 'password', 'rol', 'saldo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'saldo' => 'decimal:2',
        ];
    }

    /**
     * Determine if the user has the admin role.
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Comprueba si el usuario tiene saldo suficiente para un importe.
     */
    public function tieneSaldoSuficiente(float $monto): bool
    {
        return (float) $this->saldo >= $monto;
    }

    /**
     * Debita saldo del usuario.
     */
    public function debitarSaldo(float $monto): bool
    {
        if (! $this->tieneSaldoSuficiente($monto)) {
            return false;
        }

        $this->decrement('saldo', $monto);

        return true;
    }

    /**
     * Acredita saldo al usuario.
     */
    public function acreditarSaldo(float $monto): void
    {
        $this->increment('saldo', $monto);
    }

    /**
     * Get all jugadas belonging to this user.
     */
    public function jugadas(): HasMany
    {
        return $this->hasMany(Jugada::class);
    }

    /**
     * Get all audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
