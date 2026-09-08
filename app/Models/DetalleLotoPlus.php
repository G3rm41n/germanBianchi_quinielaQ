<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['jugada_id', 'numeros', 'numero_plus'])]
class DetalleLotoPlus extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'numeros' => 'array',
        ];
    }

    public function jugada(): BelongsTo
    {
        return $this->belongsTo(Jugada::class);
    }
}
