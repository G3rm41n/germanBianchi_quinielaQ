<?php

namespace Database\Factories;

use App\Models\Jugada;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Jugada>
 */
class JugadaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->cliente(),
            'modalidad' => fake()->randomElement(['quiniela', 'quini6', 'lotoplus', 'loto5', 'poceada']),
            'monto_total' => fake()->randomElement([800, 1000, 1200, 1500]),
            'precio_unitario_snapshot' => fake()->randomElement([800, 1000, 1200, 1500]),
            'estado' => 'pendiente',
            'idempotency_token' => (string) Str::uuid(),
        ];
    }

    /**
     * State for a pending jugada.
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'pendiente',
        ]);
    }

    /**
     * State for a processed jugada.
     */
    public function procesado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'procesado',
        ]);
    }

    /**
     * State for a jugada with a failed SMTP notification.
     */
    public function errorEnvio(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'error_envio',
        ]);
    }
}
