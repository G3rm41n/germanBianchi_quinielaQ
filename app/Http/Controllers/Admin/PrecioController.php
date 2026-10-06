<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrecioHistorico;
use Illuminate\Http\Request;

class PrecioController extends Controller
{
    public function update(Request $request, string $tipo_juego)
    {
        $validated = $request->validate([
            'valor' => ['required', 'numeric', 'min:1'],
        ]);

        $precioActual = PrecioHistorico::where('tipo_juego', $tipo_juego)->vigente()->first();

        if ($precioActual && $precioActual->valor == $validated['valor']) {
            return back()->with('info', 'El precio ingresado es igual al vigente.');
        }

        if ($precioActual) {
            $precioActual->update(['fecha_fin' => now()]);
        }

        PrecioHistorico::create([
            'tipo_juego' => $tipo_juego,
            'valor' => $validated['valor'],
            'fecha_inicio' => now(),
        ]);

        return back()->with('success', "Precio de $tipo_juego actualizado correctamente.");
    }
}
