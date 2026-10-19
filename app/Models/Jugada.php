<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'monto_total', 'estado', 'idempotency_token', 'resultado', 'monto_premio'])]
class Jugada extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'monto_premio' => 'decimal:2',
            'precio_unitario_snapshot' => 'decimal:2',
        ];
    }

    // =========================================================================
    // Relations
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detallesQuiniela(): HasMany
    {
        return $this->hasMany(DetalleQuiniela::class);
    }

    public function detallesQuini6(): HasMany
    {
        return $this->hasMany(DetalleQuini6::class);
    }

    public function detallesLotoPlus(): HasMany
    {
        return $this->hasMany(DetalleLotoPlus::class);
    }

    public function detallesLoto5(): HasMany
    {
        return $this->hasMany(DetalleLoto5::class);
    }

    public function detallesPoceada(): HasMany
    {
        return $this->hasMany(DetalleQuinielaPoceada::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeProcesado(Builder $query): Builder
    {
        return $query->where('estado', 'procesado');
    }

    public function scopeErrorEnvio(Builder $query): Builder
    {
        return $query->where('estado', 'error_envio');
    }

    public function scopeGanadora(Builder $query): Builder
    {
        return $query->where('resultado', 'ganadora');
    }

    public function scopeNoGanadora(Builder $query): Builder
    {
        return $query->where('resultado', 'no_ganadora');
    }

    public function scopePendienteSorteo(Builder $query): Builder
    {
        return $query->where('resultado', 'pendiente_sorteo');
    }

    // =========================================================================
    // Accessors & Helpers
    // =========================================================================

    public function getBalanceNetoAttribute(): float
    {
        return (float) ($this->monto_premio - $this->monto_total);
    }

    public function esGanadora(): bool
    {
        return $this->resultado === 'ganadora';
    }
}
