<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'juego',
    'fecha_sorteo',
    'numero_sorteo',
    'jurisdiccion',
    'turno',
    'extracto',
    'tabla_premios',
    'estado',
    'total_boletas_evaluadas',
    'total_boletas_ganadoras',
    'total_premios_liquidados',
    'created_by',
    'escrutado_en',
])]
class Sorteo extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha_sorteo' => 'date',
            'extracto' => 'array',
            'tabla_premios' => 'array',
            'total_premios_liquidados' => 'decimal:2',
            'total_boletas_evaluadas' => 'integer',
            'total_boletas_ganadoras' => 'integer',
            'escrutado_en' => 'datetime',
        ];
    }

    public function jugadas(): HasMany
    {
        return $this->hasMany(Jugada::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeEscrutado(Builder $query): Builder
    {
        return $query->where('estado', 'escrutado');
    }

    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function getNombreJuegoAttribute(): string
    {
        return match ($this->juego) {
            'quiniela' => 'Quiniela',
            'quini6' => 'Quini 6',
            'lotoplus' => 'Loto Plus',
            'loto5' => 'Loto 5',
            'poceada' => 'Quiniela Poceada',
            default => ucfirst((string) $this->juego),
        };
    }

    public function getNombreJurisdiccionAttribute(): ?string
    {
        if (! $this->jurisdiccion) {
            return null;
        }

        return match ($this->jurisdiccion) {
            'nacion' => 'Nación / Ciudad',
            'provincia' => 'Provincia Bs. As.',
            'santa_fe' => 'Santa Fe',
            'cordoba' => 'Córdoba',
            default => ucfirst((string) $this->jurisdiccion),
        };
    }

    public function getNombreTurnoAttribute(): ?string
    {
        if (! $this->turno) {
            return null;
        }

        return match ($this->turno) {
            'previa' => 'La Previa',
            'primera' => 'Primera',
            'matutina' => 'Matutina',
            'vespertina' => 'Vespertina',
            'nocturna' => 'Nocturna',
            default => ucfirst((string) $this->turno),
        };
    }
}
