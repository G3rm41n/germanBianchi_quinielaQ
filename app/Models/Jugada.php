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

    /**
     * Scope para aplicar filtros combinados de categoría, fecha, estado y resultado.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        if (! empty($filtros['categoria'])) {
            match ($filtros['categoria']) {
                'quiniela' => $query->whereHas('detallesQuiniela'),
                'quini6' => $query->whereHas('detallesQuini6'),
                'lotoplus' => $query->whereHas('detallesLotoPlus'),
                'loto5' => $query->whereHas('detallesLoto5'),
                'poceada' => $query->whereHas('detallesPoceada'),
                default => null,
            };
        }

        if (! empty($filtros['fecha'])) {
            $query->whereDate('created_at', $filtros['fecha']);
        } else {
            if (! empty($filtros['fecha_desde'])) {
                $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
            }
            if (! empty($filtros['fecha_hasta'])) {
                $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
            }
        }

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['resultado'])) {
            $query->where('resultado', $filtros['resultado']);
        }

        return $query;
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
