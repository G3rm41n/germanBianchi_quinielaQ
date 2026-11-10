<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sorteo;
use App\Services\Escrutinio\EscrutinioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SorteoAdminController extends Controller
{
    public function __construct(
        public EscrutinioService $escrutinioService
    ) {}

    public function index(Request $request): View
    {
        $query = Sorteo::query()->with('creator')->latest('fecha_sorteo');

        if ($request->filled('juego')) {
            $query->where('juego', $request->juego);
        }

        if ($request->filled('turno')) {
            $query->where('turno', $request->turno);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_sorteo', $request->fecha);
        }

        $sorteos = $query->paginate(15)->withQueryString();

        $metricas = [
            'total' => Sorteo::count(),
            'pendientes' => Sorteo::where('estado', 'pendiente')->count(),
            'escrutados' => Sorteo::where('estado', 'escrutado')->count(),
            'total_premios' => (float) Sorteo::where('estado', 'escrutado')->sum('total_premios_liquidados'),
        ];

        return view('admin.sorteos.index', compact('sorteos', 'metricas'));
    }

    public function create(): View
    {
        return view('admin.sorteos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $juego = $request->input('juego');

        // Normalizar inputs con prefijo de cada panel para evitar colisiones
        if ($juego === 'quini6' && $request->has('quini6_numeros')) {
            $request->merge([
                'numeros' => $request->input('quini6_numeros'),
                'tabla_premios' => $request->input('quini6_premios'),
            ]);
        } elseif ($juego === 'lotoplus' && $request->has('lotoplus_numeros')) {
            $request->merge([
                'numeros' => $request->input('lotoplus_numeros'),
                'numero_plus' => $request->input('lotoplus_numero_plus'),
                'tabla_premios' => $request->input('lotoplus_premios'),
            ]);
        } elseif ($juego === 'loto5' && $request->has('loto5_numeros')) {
            $request->merge([
                'numeros' => $request->input('loto5_numeros'),
                'tabla_premios' => $request->input('loto5_premios'),
            ]);
        } elseif ($juego === 'poceada' && $request->has('poceada_numeros')) {
            $request->merge([
                'numeros' => $request->input('poceada_numeros'),
                'tabla_premios' => $request->input('poceada_premios'),
            ]);
        }

        $reglasComunes = [
            'juego' => ['required', Rule::in(['quiniela', 'quini6', 'lotoplus', 'loto5', 'poceada'])],
            'fecha_sorteo' => ['required', 'date'],
            'numero_sorteo' => ['nullable', 'string', 'max:50'],
        ];

        $reglasPorJuego = match ($juego) {
            'quiniela' => [
                'jurisdiccion' => ['required', Rule::in(['nacion', 'provincia', 'santa_fe', 'cordoba'])],
                'turno' => ['required', Rule::in(['previa', 'primera', 'matutina', 'vespertina', 'nocturna'])],
                'extracto' => ['required', 'array', 'size:20'],
                'extracto.*' => ['required', 'string', 'regex:/^\d{1,4}$/'],
            ],
            'quini6' => [
                'numeros' => ['required', 'array', 'size:6'],
                'numeros.*' => ['required', 'integer', 'between:0,45', 'distinct'],
                'tabla_premios' => ['required', 'array'],
                'tabla_premios.6' => ['required', 'numeric', 'min:0'],
                'tabla_premios.5' => ['required', 'numeric', 'min:0'],
                'tabla_premios.4' => ['required', 'numeric', 'min:0'],
            ],
            'lotoplus' => [
                'numeros' => ['required', 'array', 'size:6'],
                'numeros.*' => ['required', 'integer', 'between:0,41', 'distinct'],
                'numero_plus' => ['required', 'integer', 'between:0,9'],
                'tabla_premios' => ['required', 'array'],
                'tabla_premios.6' => ['required', 'numeric', 'min:0'],
                'tabla_premios.5' => ['required', 'numeric', 'min:0'],
                'tabla_premios.4' => ['required', 'numeric', 'min:0'],
                'tabla_premios.plus_bonus' => ['nullable', 'numeric', 'min:0'],
            ],
            'loto5' => [
                'numeros' => ['required', 'array', 'size:5'],
                'numeros.*' => ['required', 'integer', 'between:0,36', 'distinct'],
                'tabla_premios' => ['required', 'array'],
                'tabla_premios.5' => ['required', 'numeric', 'min:0'],
                'tabla_premios.4' => ['required', 'numeric', 'min:0'],
                'tabla_premios.3' => ['required', 'numeric', 'min:0'],
            ],
            'poceada' => [
                'numeros' => ['required', 'array', 'min:15', 'max:20'],
                'numeros.*' => ['required', 'string', 'regex:/^\d{1,2}$/', 'distinct'],
                'tabla_premios' => ['required', 'array'],
                'tabla_premios.8' => ['required', 'numeric', 'min:0'],
                'tabla_premios.7' => ['required', 'numeric', 'min:0'],
                'tabla_premios.6' => ['required', 'numeric', 'min:0'],
            ],
            default => [],
        };

        $mensajes = [
            'juego.required' => 'El tipo de juego es obligatorio.',
            'fecha_sorteo.required' => 'La fecha del sorteo es obligatoria.',
            'extracto.required' => 'Debes ingresar los 20 números del extracto de Quiniela.',
            'extracto.size' => 'El extracto de Quiniela debe contener exactamente 20 números.',
            'extracto.*.required' => 'Cada una de las 20 posiciones de la Quiniela debe tener un número.',
            'extracto.*.regex' => 'Los números de Quiniela deben contener entre 1 y 4 dígitos numéricos.',
            'numeros.required' => 'Debes ingresar los números oficiales sorteados.',
            'numeros.size' => 'Debes ingresar exactamente :size números sorteados.',
            'numeros.*.required' => 'Cada casilla de número sorteado debe completarse.',
            'numeros.*.distinct' => 'Los números sorteados no pueden repetirse.',
            'numeros.*.between' => 'Cada número debe estar dentro del rango permitido para esta modalidad.',
            'numero_plus.required' => 'El número Plus es obligatorio para Loto Plus.',
            'numero_plus.between' => 'El número Plus debe ser un dígito entre 0 y 9.',
            'tabla_premios.required' => 'Debes configurar los importes de premios a liquidar.',
            'tabla_premios.*.required' => 'Todos los campos de premios son obligatorios.',
            'tabla_premios.*.min' => 'El importe de premio no puede ser negativo.',
            'jurisdiccion.required' => 'La jurisdicción es obligatoria para la Quiniela.',
            'turno.required' => 'El turno es obligatorio para la Quiniela.',
        ];

        $validated = $request->validate(array_merge($reglasComunes, $reglasPorJuego), $mensajes);

        // Armar el extracto según el tipo de juego
        $extractoFinal = match ($juego) {
            'quiniela' => $validated['extracto'],
            'quini6' => ['numeros' => array_map('intval', $validated['numeros'])],
            'lotoplus' => [
                'numeros' => array_map('intval', $validated['numeros']),
                'numero_plus' => (int) $validated['numero_plus'],
            ],
            'loto5' => ['numeros' => array_map('intval', $validated['numeros'])],
            'poceada' => ['numeros' => array_map(fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT), $validated['numeros'])],
            default => [],
        };

        $sorteo = Sorteo::create([
            'juego' => $juego,
            'fecha_sorteo' => $validated['fecha_sorteo'],
            'numero_sorteo' => $validated['numero_sorteo'] ?? null,
            'jurisdiccion' => $validated['jurisdiccion'] ?? null,
            'turno' => $validated['turno'] ?? null,
            'extracto' => $extractoFinal,
            'tabla_premios' => $validated['tabla_premios'] ?? null,
            'estado' => 'pendiente',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.sorteos.show', $sorteo)
            ->with('success', 'Sorteo registrado correctamente. Ahora puedes previsualizar y ejecutar el escrutinio.');
    }

    public function show(Sorteo $sorteo): View
    {
        $sorteo->load('creator');

        $simulacion = null;
        $jugadasEscrutadas = null;

        if ($sorteo->estado === 'pendiente') {
            $simulacion = $this->escrutinioService->simular($sorteo);
        } else {
            $jugadasEscrutadas = $sorteo->jugadas()->with('user')->paginate(15);
        }

        return view('admin.sorteos.show', compact('sorteo', 'simulacion', 'jugadasEscrutadas'));
    }

    public function escrutar(Sorteo $sorteo): RedirectResponse
    {
        if ($sorteo->estado !== 'pendiente') {
            return back()->with('error', 'Este sorteo ya fue escrutado o está anulado.');
        }

        $res = $this->escrutinioService->escrutar($sorteo, auth()->user());

        return redirect()->route('admin.sorteos.show', $sorteo)
            ->with('success', "¡Sorteo escrutado con éxito! Se evaluaron {$res['total_boletas']} boletas ({$res['total_ganadoras']} ganadoras) y se acreditaron $".number_format($res['total_premios'], 2, ',', '.').' a los usuarios.');
    }

    public function revertir(Sorteo $sorteo): RedirectResponse
    {
        if ($sorteo->estado !== 'escrutado') {
            return back()->with('error', 'Solo se pueden revertir sorteos que hayan sido escrutados.');
        }

        $this->escrutinioService->revertir($sorteo, auth()->user());

        return redirect()->route('admin.sorteos.show', $sorteo)
            ->with('info', 'El sorteo ha sido revertido. Los premios fueron debitados de las billeteras y las boletas volvieron a estar en sorteo.');
    }

    public function destroy(Sorteo $sorteo): RedirectResponse
    {
        if ($sorteo->estado === 'escrutado') {
            return back()->with('error', 'No se puede eliminar un sorteo ya escrutado. Primero debes revertirlo.');
        }

        $sorteo->delete();

        return redirect()->route('admin.sorteos.index')
            ->with('success', 'Sorteo eliminado correctamente.');
    }
}
