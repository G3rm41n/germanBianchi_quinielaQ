<?php

namespace App\Http\Controllers;

use App\Models\Sorteo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SorteoPublicController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sorteo::query()
            ->latest('fecha_sorteo')
            ->latest('id');

        if ($request->filled('juego')) {
            $query->where('juego', $request->juego);
        }

        if ($request->filled('turno')) {
            $query->where('turno', $request->turno);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_sorteo', $request->fecha);
        }

        $sorteos = $query->paginate(10)->withQueryString();

        return view('sorteos.index', compact('sorteos'));
    }

    public function show(Sorteo $sorteo): View
    {
        return view('sorteos.show', compact('sorteo'));
    }
}
