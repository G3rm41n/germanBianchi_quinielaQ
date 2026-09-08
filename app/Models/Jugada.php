<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'modalidad', 'monto_total', 'precio_unitario_snapshot', 'estado', 'idempotency_token'])]
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

    public function detalleQuiniela(): HasOne
    {
        return $this->hasOne(DetalleQuiniela::class);
    }

    public function detalleQuini6(): HasOne
    {
        return $this->hasOne(DetalleQuini6::class);
    }

    public function detalleLotoPlus(): HasOne
    {
        return $this->hasOne(DetalleLotoPlus::class);
    }

    public function detalleLoto5(): HasOne
    {
        return $this->hasOne(DetalleLoto5::class);
    }

    public function detallePoceada(): HasOne
    {
        return $this->hasOne(DetalleQuinielaPoceada::class);
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
}
