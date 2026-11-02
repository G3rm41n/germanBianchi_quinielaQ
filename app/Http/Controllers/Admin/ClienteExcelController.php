<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExcelClienteJugadaService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClienteExcelController extends Controller
{
    /**
     * Exporta los clientes y sus jugadas detalladas a un archivo Excel (.xlsx).
     */
    public function exportar(Request $request, ExcelClienteJugadaService $excelService): StreamedResponse
    {
        return $excelService->exportarClientesYJugadas($request->all());
    }

    /**
     * Descarga la plantilla oficial en blanco con datos de ejemplo para importación.
     */
    public function descargarPlantilla(ExcelClienteJugadaService $excelService): StreamedResponse
    {
        return $excelService->descargarPlantillaEjemplo();
    }

    /**
     * Procesa la importación de un archivo Excel con clientes y jugadas.
     */
    public function importar(Request $request, ExcelClienteJugadaService $excelService): RedirectResponse
    {
        $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240', // Máximo 10MB
            ],
        ], [
            'archivo.required' => 'Debe seleccionar un archivo Excel para importar.',
            'archivo.mimes' => 'El archivo debe tener formato Excel (.xlsx o .xls).',
            'archivo.max' => 'El archivo no debe superar los 10MB.',
        ]);

        try {
            $resultado = $excelService->importarDesdeExcel($request->file('archivo'));

            $mensaje = "Importación completada con éxito: {$resultado['clientes_creados']} clientes creados, {$resultado['clientes_actualizados']} actualizados y {$resultado['jugadas_creadas']} jugadas incorporadas.";

            if (! empty($resultado['advertencias'])) {
                $mensaje .= ' Nota: Se omitieron algunas filas con datos incompletos.';
            }

            return redirect()->route('admin.users.index')->with('success', $mensaje);
        } catch (Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Ocurrió un error al procesar el archivo Excel: '.$e->getMessage());
        }
    }
}

