<?php

namespace App\Models;

use App\Notifications\CustomResetPasswordNotification;
use App\Notifications\CustomVerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'dni', 'email', 'password', 'rol', 'saldo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Enviar notificación personalizada para restablecer contraseña.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Enviar notificación personalizada para verificar dirección de correo.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmailNotification);
    }

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

    /**
     * Scope para filtrar clientes por búsqueda de texto (nombre o apellido), cantidad de jugadas y volumen.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function scopeFiltrarClientes(Builder $query, array $filtros): Builder
    {
        if (! empty($filtros['buscar'])) {
            $busqueda = trim((string) $filtros['buscar']);
            $terminos = preg_split('/\s+/', $busqueda, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            $query->where(function (Builder $subQuery) use ($terminos, $busqueda) {
                $subQuery->where(function (Builder $nameQuery) use ($terminos) {
                    foreach ($terminos as $termino) {
                        $nameQuery->where('name', 'LIKE', '%'.$termino.'%');
                    }
                });

                $subQuery->orWhere('dni', 'LIKE', '%'.$busqueda.'%')
                    ->orWhere('email', 'LIKE', '%'.$busqueda.'%');
            });
        }

        if (! empty($filtros['inicial_nombre'])) {
            $query->where('name', 'LIKE', trim($filtros['inicial_nombre']).'%');
        }

        if (! empty($filtros['inicial_apellido'])) {
            $query->where('name', 'LIKE', '% '.trim($filtros['inicial_apellido']).'%');
        }

        if (! empty($filtros['min_jugadas'])) {
            $query->has('jugadas', '>=', (int) $filtros['min_jugadas']);
        }

        if (! empty($filtros['min_volumen']) && is_numeric($filtros['min_volumen'])) {
            $minVol = (float) $filtros['min_volumen'];
            $query->whereRaw("(SELECT COALESCE(SUM(monto_total), 0) FROM jugadas WHERE jugadas.user_id = users.id) >= {$minVol}");
        }

        return $query;
    }
}
