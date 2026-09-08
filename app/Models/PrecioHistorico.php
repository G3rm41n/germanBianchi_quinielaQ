<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tipo_juego', 'valor', 'fecha_inicio', 'fecha_fin'])]
class PrecioHistorico extends Model
{
    use HasFactory;

    protected $table = 'precios_historicos';

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    /**
     * Scope to get the currently active price (fecha_fin IS NULL).
     */
    public function scopeVigente(Builder $query): Builder
    {
        return $query->whereNull('fecha_fin');
    }

    /**
     * Get the current active price for a given game type.
     */
    public static function precioActual(string $tipoJuego): ?self
    {
        return static::vigente()->where('tipo_juego', $tipoJuego)->latest('fecha_inicio')->first();
    }
}
