<?php

namespace Database\Seeders;

use App\Models\Jugada;
use App\Models\PrecioHistorico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PreciosSeeder::class);

        $precioQuini6 = PrecioHistorico::precioActual('quini6')?->valor ?? 1200;
        $precioLotoPlus = PrecioHistorico::precioActual('lotoplus')?->valor ?? 1500;
        $precioLoto5 = PrecioHistorico::precioActual('loto5')?->valor ?? 800;
        $precioPoceada = PrecioHistorico::precioActual('poceada')?->valor ?? 1000;

        $clientesData = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@ejemplo.com',
                'dni' => '30123456',
                'saldo' => 17500.00,
            ],
            [
                'name' => 'María Gómez',
                'email' => 'maria.gomez@ejemplo.com',
                'dni' => '32987654',
                'saldo' => 85000.00,
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos.lopez@ejemplo.com',
                'dni' => '28456123',
                'saldo' => 12000.00,
            ],
            [
                'name' => 'Laura Martínez',
                'email' => 'laura.martinez@ejemplo.com',
                'dni' => '35789456',
                'saldo' => 45000.00,
            ],
            [
                'name' => 'Lucas Rodríguez',
                'email' => 'lucas.rodriguez@ejemplo.com',
                'dni' => '38123987',
                'saldo' => 30000.00,
            ],
        ];

        foreach ($clientesData as $index => $cData) {
            $user = User::updateOrCreate(
                ['email' => $cData['email']],
                [
                    'name' => $cData['name'],
                    'dni' => $cData['dni'],
                    'rol' => 'cliente',
                    'saldo' => $cData['saldo'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );

            // Generamos jugadas históricas específicas para cada cliente si no tiene aún
            if ($user->jugadas()->count() === 0) {
                $this->crearJugadasParaCliente($user, $index, $precioQuini6, $precioLotoPlus, $precioLoto5, $precioPoceada);
            }
        }
    }

    /**
     * Crea un paquete de boletas realistas y variadas para el cliente especificado.
     */
    private function crearJugadasParaCliente(
        User $user,
        int $perfilIndex,
        float $precioQuini6,
        float $precioLotoPlus,
        float $precioLoto5,
        float $precioPoceada
    ): void {
        match ($perfilIndex) {
            // Juan Pérez: Quiniela ganadora, Quini 6 perdida, Loto Plus en sorteo
            0 => $this->crearPerfilJuanPerez($user, $precioQuini6, $precioLotoPlus),

            // María Gómez: Quini 6 ganadora importante, Poceada perdida, Quiniela en sorteo
            1 => $this->crearPerfilMariaGomez($user, $precioQuini6, $precioPoceada),

            // Carlos López: Loto 5 ganadora, Loto Plus perdida, Quiniela con error SMTP
            2 => $this->crearPerfilCarlosLopez($user, $precioLoto5, $precioLotoPlus),

            // Laura Martínez: Poceada ganadora, Quini 6 perdida, Loto 5 en sorteo
            3 => $this->crearPerfilLauraMartinez($user, $precioPoceada, $precioQuini6, $precioLoto5),

            // Lucas Rodríguez: Loto Plus ganadora, Quiniela perdida, Quini 6 en sorteo
            default => $this->crearPerfilLucasRodriguez($user, $precioLotoPlus, $precioQuini6),
        };
    }

    private function crearPerfilJuanPerez(User $user, float $precioQuini6, float $precioLotoPlus): void
    {
        // 1. Ganadora (Quiniela a la cabeza)
        $j1 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => 500.00,
            'monto_premio' => 17500.00,
            'estado' => 'procesado',
            'resultado' => 'ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(8)->setTime(14, 30),
            'updated_at' => Carbon::now()->subDays(8)->setTime(14, 30),
        ]);
        $j1->detallesQuiniela()->create([
            'numero' => '0742',
            'posicion' => 1,
            'jurisdiccion' => 'nacion',
            'importe' => 500.00,
        ]);

        // 2. No Ganadora (Quini 6)
        $j2 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioQuini6,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'no_ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(4)->setTime(18, 15),
            'updated_at' => Carbon::now()->subDays(4)->setTime(18, 15),
        ]);
        $j2->detallesQuini6()->create([
            'numeros' => [4, 11, 19, 25, 33, 42],
        ]);

        // 3. En Sorteo (Loto Plus)
        $j3 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioLotoPlus,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'pendiente_sorteo',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subHours(5),
            'updated_at' => Carbon::now()->subHours(5),
        ]);
        $j3->detallesLotoPlus()->create([
            'numeros' => [7, 14, 21, 28, 35, 41],
            'numero_plus' => 3,
        ]);
    }

    private function crearPerfilMariaGomez(User $user, float $precioQuini6, float $precioPoceada): void
    {
        // 1. Ganadora Mayor (Quini 6)
        $j1 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioQuini6,
            'monto_premio' => 85000.00,
            'estado' => 'procesado',
            'resultado' => 'ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(12)->setTime(21, 00),
            'updated_at' => Carbon::now()->subDays(12)->setTime(21, 00),
        ]);
        $j1->detallesQuini6()->create([
            'numeros' => [2, 9, 15, 23, 31, 40],
        ]);

        // 2. No Ganadora (Poceada)
        $j2 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioPoceada,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'no_ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(6)->setTime(16, 45),
            'updated_at' => Carbon::now()->subDays(6)->setTime(16, 45),
        ]);
        $j2->detallesPoceada()->create([
            'numeros' => [5, 12, 28, 34, 49, 63, 77, 91],
        ]);

        // 3. En Sorteo (Quiniela a los 5)
        $j3 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => 2000.00,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'pendiente_sorteo',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subHours(2),
            'updated_at' => Carbon::now()->subHours(2),
        ]);
        for ($p = 1; $p <= 5; $p++) {
            $j3->detallesQuiniela()->create([
                'numero' => '8819',
                'posicion' => $p,
                'jurisdiccion' => 'provincia',
                'importe' => 400.00,
            ]);
        }
    }

    private function crearPerfilCarlosLopez(User $user, float $precioLoto5, float $precioLotoPlus): void
    {
        // 1. Ganadora (Loto 5)
        $j1 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioLoto5,
            'monto_premio' => 12000.00,
            'estado' => 'procesado',
            'resultado' => 'ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(10)->setTime(11, 20),
            'updated_at' => Carbon::now()->subDays(10)->setTime(11, 20),
        ]);
        $j1->detallesLoto5()->create([
            'numeros' => [3, 11, 18, 24, 30],
        ]);

        // 2. No Ganadora (Loto Plus)
        $j2 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioLotoPlus,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'no_ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(5)->setTime(19, 10),
            'updated_at' => Carbon::now()->subDays(5)->setTime(19, 10),
        ]);
        $j2->detallesLotoPlus()->create([
            'numeros' => [6, 13, 20, 27, 34, 44],
            'numero_plus' => 8,
        ]);

        // 3. Error SMTP (Quiniela a los 10)
        $j3 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => 3000.00,
            'monto_premio' => 0.0,
            'estado' => 'error_envio',
            'resultado' => 'pendiente_sorteo',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subHours(1),
            'updated_at' => Carbon::now()->subHours(1),
        ]);
        for ($p = 1; $p <= 10; $p++) {
            $j3->detallesQuiniela()->create([
                'numero' => '1420',
                'posicion' => $p,
                'jurisdiccion' => 'santa_fe',
                'importe' => 300.00,
            ]);
        }
    }

    private function crearPerfilLauraMartinez(User $user, float $precioPoceada, float $precioQuini6, float $precioLoto5): void
    {
        // 1. Ganadora (Poceada)
        $j1 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioPoceada,
            'monto_premio' => 45000.00,
            'estado' => 'procesado',
            'resultado' => 'ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(7)->setTime(15, 40),
            'updated_at' => Carbon::now()->subDays(7)->setTime(15, 40),
        ]);
        $j1->detallesPoceada()->create([
            'numeros' => [8, 17, 26, 35, 54, 62, 73, 89],
        ]);

        // 2. No Ganadora (Quini 6)
        $j2 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioQuini6,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'no_ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(3)->setTime(12, 00),
            'updated_at' => Carbon::now()->subDays(3)->setTime(12, 00),
        ]);
        $j2->detallesQuini6()->create([
            'numeros' => [1, 10, 18, 27, 36, 44],
        ]);

        // 3. En Sorteo (Loto 5)
        $j3 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioLoto5,
            'monto_premio' => 0.0,
            'estado' => 'pendiente',
            'resultado' => 'pendiente_sorteo',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subHours(4),
            'updated_at' => Carbon::now()->subHours(4),
        ]);
        $j3->detallesLoto5()->create([
            'numeros' => [2, 9, 16, 23, 31],
        ]);
    }

    private function crearPerfilLucasRodriguez(User $user, float $precioLotoPlus, float $precioQuini6): void
    {
        // 1. Ganadora (Loto Plus)
        $j1 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioLotoPlus,
            'monto_premio' => 30000.00,
            'estado' => 'procesado',
            'resultado' => 'ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(9)->setTime(20, 15),
            'updated_at' => Carbon::now()->subDays(9)->setTime(20, 15),
        ]);
        $j1->detallesLotoPlus()->create([
            'numeros' => [5, 12, 19, 26, 33, 40],
            'numero_plus' => 5,
        ]);

        // 2. No Ganadora (Quiniela del 1 al 20)
        $j2 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => 4000.00,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'no_ganadora',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subDays(2)->setTime(13, 00),
            'updated_at' => Carbon::now()->subDays(2)->setTime(13, 00),
        ]);
        for ($p = 1; $p <= 20; $p++) {
            $j2->detallesQuiniela()->create([
                'numero' => '3541',
                'posicion' => $p,
                'jurisdiccion' => 'cordoba',
                'importe' => 200.00,
            ]);
        }

        // 3. En Sorteo (Quini 6)
        $j3 = Jugada::create([
            'user_id' => $user->id,
            'monto_total' => $precioQuini6,
            'monto_premio' => 0.0,
            'estado' => 'procesado',
            'resultado' => 'pendiente_sorteo',
            'idempotency_token' => (string) Str::uuid(),
            'created_at' => Carbon::now()->subHours(6),
            'updated_at' => Carbon::now()->subHours(6),
        ]);
        $j3->detallesQuini6()->create([
            'numeros' => [3, 12, 21, 30, 39, 45],
        ]);
    }
}
