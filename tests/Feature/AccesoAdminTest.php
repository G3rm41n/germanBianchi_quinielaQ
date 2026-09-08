<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Control de acceso al backoffice', function () {

    it('un invitado no puede acceder al admin y es redirigido al login', function () {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    });

    it('un cliente autenticado no puede acceder al admin', function () {
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($cliente)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    });

    it('un admin autenticado puede acceder al backoffice', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Panel de Administración');
    });

    it('un invitado no puede acceder al panel de jugadas y es redirigido al login', function () {
        $this->get(route('jugadas.index'))
            ->assertRedirect(route('login'));
    });

    it('un cliente puede acceder a su panel de jugadas', function () {
        $cliente = User::factory()->cliente()->create();

        $this->actingAs($cliente)
            ->get(route('jugadas.index'))
            ->assertStatus(200)
            ->assertSee('Mis Jugadas');
    });
});
