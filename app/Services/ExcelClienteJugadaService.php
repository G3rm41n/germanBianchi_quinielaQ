<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Jugada;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelClienteJugadaService
{
    /**
     * Exporta todos los clientes (o clientes filtrados) con sus jugadas detalladas a un archivo Excel (.xlsx).
     *
     * @param  array<string, mixed>  $filtros
     */
    public function exportarClientesYJugadas(array $filtros = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;

        // Estilos corporativos base
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'D4AF37'], // Dorado
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'], // Gris oscuro / Carbón
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '374151'],
                ],
            ],
        ];

        // ---------------------------------------------------------------------
        // 1. Hoja 1: Clientes
        // ---------------------------------------------------------------------
        $sheetClientes = $spreadsheet->getActiveSheet();
        $sheetClientes->setTitle('Clientes');

        $headersClientes = [
            'ID Cliente',
            'Nombre Completo',
            'DNI',
            'Email',
            'Saldo Disponible ($)',
            'Jugadas Totales',
            'Volumen Total Apostado ($)',
            'Fecha Registro',
        ];

        $sheetClientes->fromArray($headersClientes, null, 'A1');
        $sheetClientes->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheetClientes->getRowDimension(1)->setRowHeight(28);

        $clientesQuery = User::where('rol', 'cliente')
            ->filtrarClientes($filtros)
            ->withCount('jugadas')
            ->withSum('jugadas', 'monto_total')
            ->orderBy('id', 'asc');

        $clientes = $clientesQuery->get();

        $fila = 2;
        foreach ($clientes as $c) {
            $sheetClientes->setCellValueExplicit("A{$fila}", $c->id, DataType::TYPE_NUMERIC);
            $sheetClientes->setCellValue("B{$fila}", $c->name);
            $sheetClientes->setCellValueExplicit("C{$fila}", (string) $c->dni, DataType::TYPE_STRING);
            $sheetClientes->setCellValue("D{$fila}", $c->email);
            $sheetClientes->setCellValue("E{$fila}", (float) $c->saldo);
            $sheetClientes->setCellValue("F{$fila}", (int) $c->jugadas_count);
            $sheetClientes->setCellValue("G{$fila}", (float) ($c->jugadas_sum_monto_total ?? 0));
            $sheetClientes->setCellValue("H{$fila}", $c->created_at ? $c->created_at->format('d/m/Y H:i') : '-');

            // Formato de moneda
            $sheetClientes->getStyle("E{$fila}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
            $sheetClientes->getStyle("G{$fila}")->getNumberFormat()->setFormatCode('"$"#,##0.00');

            $fila++;
        }

        foreach (range('A', 'H') as $col) {
            $sheetClientes->getColumnDimension($col)->setAutoSize(true);
        }

        // ---------------------------------------------------------------------
        // 2. Hoja 2: Jugadas Detalladas
        // ---------------------------------------------------------------------
        $sheetJugadas = $spreadsheet->createSheet();
        $sheetJugadas->setTitle('Jugadas Detalladas');

        $headersJugadas = [
            'Ticket #',
            'ID Cliente',
            'Cliente',
            'DNI',
            'Fecha Boleta',
            'Modalidades',
            'Detalle de Apuestas',
            'Monto Invertido ($)',
            'Estado Agencia',
            'Resultado Sorteo',
            'Premio Cobrado ($)',
        ];

        $sheetJugadas->fromArray($headersJugadas, null, 'A1');
        $sheetJugadas->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheetJugadas->getRowDimension(1)->setRowHeight(28);

        $jugadas = Jugada::with([
            'user',
            'detallesQuiniela',
            'detallesQuini6',
            'detallesLotoPlus',
            'detallesLoto5',
            'detallesPoceada',
        ])
            ->whereIn('user_id', $clientes->pluck('id'))
            ->orderByDesc('id')
            ->get();

        $filaJ = 2;
        foreach ($jugadas as $j) {
            $modalidades = [];
            $detallesTexto = [];

            foreach ($j->detallesQuiniela as $q) {
                $modalidades[] = 'Quiniela';
                $detallesTexto[] = "Quiniela: Nº {$q->numero} (Pos: {$q->posicion}, Jur: {$q->jurisdiccion}) \${$q->importe}";
            }
            foreach ($j->detallesQuini6 as $q) {
                $modalidades[] = 'Quini 6';
                $nums = is_array($q->numeros) ? implode(', ', $q->numeros) : $q->numeros;
                $detallesTexto[] = "Quini 6: [{$nums}]";
            }
            foreach ($j->detallesLotoPlus as $q) {
                $modalidades[] = 'Loto Plus';
                $nums = is_array($q->numeros) ? implode(', ', $q->numeros) : $q->numeros;
                $detallesTexto[] = "Loto Plus: [{$nums}] (+Plus: {$q->numero_plus})";
            }
            foreach ($j->detallesLoto5 as $q) {
                $modalidades[] = 'Loto 5';
                $nums = is_array($q->numeros) ? implode(', ', $q->numeros) : $q->numeros;
                $detallesTexto[] = "Loto 5: [{$nums}]";
            }
            foreach ($j->detallesPoceada as $q) {
                $modalidades[] = 'Poceada';
                $nums = is_array($q->numeros) ? implode(', ', $q->numeros) : $q->numeros;
                $detallesTexto[] = "Poceada: [{$nums}]";
            }

            $sheetJugadas->setCellValueExplicit("A{$filaJ}", $j->id, DataType::TYPE_NUMERIC);
            $sheetJugadas->setCellValueExplicit("B{$filaJ}", $j->user_id, DataType::TYPE_NUMERIC);
            $sheetJugadas->setCellValue("C{$filaJ}", $j->user?->name ?? 'Desconocido');
            $sheetJugadas->setCellValueExplicit("D{$filaJ}", (string) ($j->user?->dni ?? ''), DataType::TYPE_STRING);
            $sheetJugadas->setCellValue("E{$filaJ}", $j->created_at ? $j->created_at->format('d/m/Y H:i') : '-');
            $sheetJugadas->setCellValue("F{$filaJ}", implode(', ', array_unique($modalidades)));
            $sheetJugadas->setCellValue("G{$filaJ}", implode(' | ', $detallesTexto));
            $sheetJugadas->setCellValue("H{$filaJ}", (float) $j->monto_total);
            $sheetJugadas->setCellValue("I{$filaJ}", ucfirst($j->estado));
            $sheetJugadas->setCellValue("J{$filaJ}", ucfirst(str_replace('_', ' ', $j->resultado)));
            $sheetJugadas->setCellValue("K{$filaJ}", (float) $j->monto_premio);

            // Formato de moneda
            $sheetJugadas->getStyle("H{$filaJ}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
            $sheetJugadas->getStyle("K{$filaJ}")->getNumberFormat()->setFormatCode('"$"#,##0.00');

            $filaJ++;
        }

        foreach (range('A', 'K') as $col) {
            $sheetJugadas->getColumnDimension($col)->setAutoSize(true);
        }

        // Activamos la primera hoja por defecto
        $spreadsheet->setActiveSheetIndex(0);

        $nombreArchivo = 'reporte_clientes_jugadas_'.now()->format('Ymd_His').'.xlsx';

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Genera una plantilla modelo (.xlsx) descargable con datos de ejemplo para importar clientes y jugadas.
     */
    public function descargarPlantillaEjemplo(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'D4AF37'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '374151']]],
        ];

        // 1. Hoja Clientes
        $sheetClientes = $spreadsheet->getActiveSheet();
        $sheetClientes->setTitle('Clientes');

        $sheetClientes->fromArray([
            'ID_Temporal',
            'Nombre',
            'Apellido',
            'DNI',
            'Email',
            'Saldo_Inicial',
        ], null, 'A1');
        $sheetClientes->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheetClientes->getRowDimension(1)->setRowHeight(26);

        // Filas de ejemplo
        $sheetClientes->fromArray([
            ['CLI-01', 'Roberto', 'Gómez', '31445566', 'roberto.gomez@ejemplo.com', 15000.00],
            ['CLI-02', 'Silvia', 'Fernández', '29887766', 'silvia.f@ejemplo.com', 20000.00],
        ], null, 'A2');

        $sheetClientes->getStyle('E2:E3')->getNumberFormat()->setFormatCode('"$"#,##0.00');
        foreach (range('A', 'F') as $col) {
            $sheetClientes->getColumnDimension($col)->setAutoSize(true);
        }

        // 2. Hoja Jugadas
        $sheetJugadas = $spreadsheet->createSheet();
        $sheetJugadas->setTitle('Jugadas');

        $sheetJugadas->fromArray([
            'ID_Cliente_Ref',
            'Modalidad',
            'Numeros_O_Detalle',
            'Jurisdiccion',
            'Posicion',
            'Importe_O_Costo',
            'Estado',
            'Resultado',
            'Premio',
        ], null, 'A1');
        $sheetJugadas->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheetJugadas->getRowDimension(1)->setRowHeight(26);

        // Filas de ejemplo de jugadas
        $sheetJugadas->fromArray([
            ['CLI-01', 'quiniela', '4512', 'nacion', 1, 500.00, 'procesado', 'ganadora', 17500.00],
            ['CLI-01', 'quini6', '04, 11, 18, 25, 32, 41', '', '', 1200.00, 'procesado', 'no_ganadora', 0.00],
            ['CLI-02', 'lotoplus', '05, 12, 19, 26, 33, 40', '', '7', 1500.00, 'procesado', 'pendiente_sorteo', 0.00],
            ['CLI-02', 'loto5', '03, 11, 18, 24, 30', '', '', 800.00, 'procesado', 'ganadora', 12000.00],
            ['CLI-02', 'poceada', '02, 14, 25, 38, 49, 61, 75, 88', '', '', 1000.00, 'pendiente', 'pendiente_sorteo', 0.00],
        ], null, 'A2');

        $sheetJugadas->getStyle('F2:F6')->getNumberFormat()->setFormatCode('"$"#,##0.00');
        $sheetJugadas->getStyle('I2:I6')->getNumberFormat()->setFormatCode('"$"#,##0.00');
        foreach (range('A', 'I') as $col) {
            $sheetJugadas->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="plantilla_clientes_jugadas_ejemplo.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Importa clientes y sus jugadas desde un archivo Excel subido.
     *
     * @return array<string, mixed>
     */
    public function importarDesdeExcel(UploadedFile $archivo): array
    {
        $spreadsheet = IOFactory::load($archivo->getRealPath());

        $clientesCreados = 0;
        $clientesActualizados = 0;
        $jugadasCreadas = 0;
        $advertencias = [];

        // Mapa de referencia temporal: ID_Temporal (o DNI/Email) => User instance
        $mapaClientes = [];

        DB::transaction(function () use (
            $spreadsheet,
            &$clientesCreados,
            &$clientesActualizados,
            &$jugadasCreadas,
            &$mapaClientes,
            &$advertencias
        ) {
            // -----------------------------------------------------------------
            // Paso 1: Procesar Hoja de Clientes
            // -----------------------------------------------------------------
            $sheetClientes = $spreadsheet->getSheetByName('Clientes') ?? $spreadsheet->getSheet(0);
            $filasClientes = $sheetClientes->toArray(null, true, true, true);

            // Se asume que la fila 1 es encabezado
            for ($f = 2; $f <= count($filasClientes); $f++) {
                $fila = $filasClientes[$f];

                $idTemp = trim((string) ($fila['A'] ?? ''));
                $nombre = trim((string) ($fila['B'] ?? ''));
                $apellido = trim((string) ($fila['C'] ?? ''));
                $dni = trim((string) ($fila['D'] ?? ''));
                $email = trim((string) ($fila['E'] ?? ''));
                $saldo = ! empty($fila['F']) ? (float) $fila['F'] : 10000.00;

                if (empty($email) && empty($dni)) {
                    continue; // Fila vacía
                }

                $nombreCompleto = trim("{$nombre} {$apellido}");
                if (empty($nombreCompleto)) {
                    $nombreCompleto = 'Cliente Importado';
                }

                // Buscar por DNI o Email
                $user = User::where('dni', $dni)->orWhere('email', $email)->first();

                if ($user) {
                    $user->update([
                        'name' => $nombreCompleto ?: $user->name,
                        'saldo' => max((float) $user->saldo, $saldo),
                    ]);
                    $clientesActualizados++;
                } else {
                    $user = User::create([
                        'name' => $nombreCompleto,
                        'dni' => $dni ?: fake()->unique()->numerify('########'),
                        'email' => $email ?: fake()->unique()->safeEmail(),
                        'rol' => 'cliente',
                        'saldo' => $saldo,
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]);
                    $clientesCreados++;
                }

                // Guardar en mapa
                if (! empty($idTemp)) {
                    $mapaClientes[strtoupper($idTemp)] = $user;
                }
                if (! empty($dni)) {
                    $mapaClientes[(string) $dni] = $user;
                }
                if (! empty($email)) {
                    $mapaClientes[strtolower($email)] = $user;
                }
            }

            // -----------------------------------------------------------------
            // Paso 2: Procesar Hoja de Jugadas (si existe)
            // -----------------------------------------------------------------
            $sheetJugadas = $spreadsheet->getSheetByName('Jugadas') ?? ($spreadsheet->getSheetCount() > 1 ? $spreadsheet->getSheet(1) : null);

            if ($sheetJugadas) {
                $filasJugadas = $sheetJugadas->toArray(null, true, true, true);

                for ($f = 2; $f <= count($filasJugadas); $f++) {
                    $fila = $filasJugadas[$f];

                    $clienteRef = trim((string) ($fila['A'] ?? ''));
                    $modalidad = strtolower(trim((string) ($fila['B'] ?? '')));
                    $detalleStr = trim((string) ($fila['C'] ?? ''));
                    $jurisdiccion = strtolower(trim((string) ($fila['D'] ?? 'nacion')));
                    $posicion = (int) ($fila['E'] ?? 1);
                    $montoCosto = ! empty($fila['F']) ? (float) $fila['F'] : 1000.00;
                    $estado = strtolower(trim((string) ($fila['G'] ?? 'procesado')));
                    $resultado = strtolower(trim((string) ($fila['H'] ?? 'pendiente_sorteo')));
                    $premio = ! empty($fila['I']) ? (float) $fila['I'] : 0.00;

                    if (empty($clienteRef) || empty($modalidad)) {
                        continue;
                    }

                    // Buscar usuario en el mapa o por BD
                    $user = $mapaClientes[strtoupper($clienteRef)]
                        ?? $mapaClientes[$clienteRef]
                        ?? $mapaClientes[strtolower($clienteRef)]
                        ?? User::where('dni', $clienteRef)->orWhere('email', $clienteRef)->first();

                    if (! $user) {
                        $advertencias[] = "Fila {$f} en Jugadas: no se encontró el cliente con referencia '{$clienteRef}'. Se omitió la jugada.";

                        continue;
                    }

                    // Validar valores de estado y resultado
                    if (! in_array($estado, ['pendiente', 'procesado', 'error_envio'])) {
                        $estado = 'procesado';
                    }
                    if (! in_array($resultado, ['ganadora', 'no_ganadora', 'pendiente_sorteo'])) {
                        $resultado = 'pendiente_sorteo';
                    }

                    $jugada = Jugada::create([
                        'user_id' => $user->id,
                        'monto_total' => $montoCosto,
                        'estado' => $estado,
                        'resultado' => $resultado,
                        'monto_premio' => $resultado === 'ganadora' ? $premio : 0.00,
                        'idempotency_token' => (string) Str::uuid(),
                    ]);

                    // Parsear números separados por coma o espacio
                    $extraerNumeros = function ($str) {
                        preg_match_all('/\d+/', $str, $matches);

                        return array_map('intval', $matches[0] ?? []);
                    };

                    if ($modalidad === 'quiniela') {
                        $numero = ! empty($detalleStr) ? (string) $detalleStr : (string) rand(10, 9999);
                        $jugada->detallesQuiniela()->create([
                            'numero' => substr($numero, 0, 4),
                            'posicion' => max(1, min(20, $posicion)),
                            'jurisdiccion' => in_array($jurisdiccion, ['nacion', 'provincia', 'santa_fe', 'cordoba']) ? $jurisdiccion : 'nacion',
                            'importe' => $montoCosto,
                        ]);
                    } elseif ($modalidad === 'quini6') {
                        $nums = $extraerNumeros($detalleStr);
                        if (count($nums) < 6) {
                            $nums = [4, 11, 18, 25, 32, 41];
                        }
                        $jugada->detallesQuini6()->create([
                            'numeros' => array_slice($nums, 0, 6),
                        ]);
                    } elseif ($modalidad === 'lotoplus') {
                        $nums = $extraerNumeros($detalleStr);
                        $plus = (int) ($fila['E'] ?? 0);
                        if (count($nums) < 6) {
                            $nums = [5, 12, 19, 26, 33, 40];
                        }
                        $jugada->detallesLotoPlus()->create([
                            'numeros' => array_slice($nums, 0, 6),
                            'numero_plus' => max(0, min(9, $plus)),
                        ]);
                    } elseif ($modalidad === 'loto5') {
                        $nums = $extraerNumeros($detalleStr);
                        if (count($nums) < 5) {
                            $nums = [3, 11, 18, 24, 30];
                        }
                        $jugada->detallesLoto5()->create([
                            'numeros' => array_slice($nums, 0, 5),
                        ]);
                    } elseif ($modalidad === 'poceada') {
                        $nums = $extraerNumeros($detalleStr);
                        if (count($nums) < 8) {
                            $nums = [2, 14, 25, 38, 49, 61, 75, 88];
                        }
                        $jugada->detallesPoceada()->create([
                            'numeros' => array_slice($nums, 0, 8),
                        ]);
                    }

                    $jugadasCreadas++;
                }
            }

            // Registrar en auditoría
            AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'importacion_excel',
                'descripcion' => "Importación Excel exitosa: {$clientesCreados} clientes nuevos, {$clientesActualizados} actualizados, {$jugadasCreadas} jugadas incorporadas.",
                'ip' => request()->ip() ?: '127.0.0.1',
            ]);
        });

        return [
            'success' => true,
            'clientes_creados' => $clientesCreados,
            'clientes_actualizados' => $clientesActualizados,
            'jugadas_creadas' => $jugadasCreadas,
            'advertencias' => $advertencias,
        ];
    }
}
