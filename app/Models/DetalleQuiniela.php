<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['jugada_id', 'numero', 'posicion', 'jurisdiccion', 'importe'])]
class DetalleQuiniela extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
        ];
    }

    public function jugada(): BelongsTo
    {
        return $this->belongsTo(Jugada::class);
    }
}
