<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function index(): View
    {
        $juegos = [
            [
                'clave' => 'quiniela',
                'nombre' => 'Quiniela Tradicional',
                'descripcion' => 'Elegí un número del 0000 al 9999, tu posición y jurisdicción. El importe es libre.',
                'icono' => '🎰',
                'precio' => 'Importe libre',
            ],
            [
                'clave' => 'quini6',
                'nombre' => 'Quini 6',
                'descripcion' => 'Seleccioná 6 números del 00 al 45. Un clásico de la lotería argentina.',
                'icono' => '6️⃣',
                'precio' => '$1.200 por ticket',
            ],
            [
                'clave' => 'lotoplus',
                'nombre' => 'Loto Plus',
                'descripcion' => '6 números del 0 al 45 más un Número Plus del 0 al 9. Más chances de ganar.',
                'icono' => '➕',
                'precio' => '$1.500 por ticket',
            ],
            [
                'clave' => 'loto5',
                'nombre' => 'Loto 5',
                'descripcion' => '5 números del 0 al 36. Simple, accesible y con grandes premios.',
                'icono' => '5️⃣',
                'precio' => '$800 por ticket',
            ],
            [
                'clave' => 'poceada',
                'nombre' => 'Quiniela Poceada',
                'descripcion' => 'Seleccioná 8 números de dos cifras (del 00 al 99). El pozo acumula.',
                'icono' => '🏆',
                'precio' => '$1.000 por ticket',
            ],
        ];

        return view('home', compact('juegos'));
    }
}
