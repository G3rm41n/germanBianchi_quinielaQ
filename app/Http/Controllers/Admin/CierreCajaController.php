<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CierreCajaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CierreCajaController extends Controller
{
    public function __construct(
        public CierreCajaService $cierreCajaService
    ) {}

    public function index(Request $request): View
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        $arqueo = $this->cierreCajaService->obtenerArqueo($fecha);

        return view('admin.caja.index', compact('arqueo', 'fecha'));
    }

    public function exportarPdf(Request $request)
    {
        $fecha = $request->input('fecha', date('Y-m-d'));
        $arqueo = $this->cierreCajaService->obtenerArqueo($fecha);
        $admin = auth()->user();
        $logoPath = public_path('images/logo.jpg');

        $pdf = Pdf::loadView('pdf.cierre_diario', compact('arqueo', 'admin', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');

        $fechaClean = str_replace('-', '', $fecha);

        return $pdf->download("cierre_caja_{$fechaClean}.pdf");
    }
}
