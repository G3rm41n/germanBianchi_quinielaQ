<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Login de usuarios', function () {

    it('puede ver el formulario de login', function () {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('Iniciar sesión');
    });

    it('un cliente puede iniciar sesión y va a jugadas', function () {
        $user = User::factory()->cliente()->create([
            'password' => bcrypt('Password123'),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Password123',
        ])
            ->assertRedirect(route('jugadas.index'));

        $this->assertAuthenticatedAs($user);
    });

    it('un admin puede iniciar sesión y va al backoffice', function () {
        $admin = User::factory()->admin()->create([
            'password' => bcrypt('Admin1234!'),
        ]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'Admin1234!',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    });

    it('falla con credenciales incorrectas', function () {
        User::factory()->create([
            'email' => 'real@test.com',
            'password' => bcrypt('CorrectPass123'),
        ]);

        $this->post(route('login.store'), [
            'email' => 'real@test.com',
            'password' => 'WrongPassword',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('puede cerrar sesión correctamente', function () {
        $user = User::factory()->cliente()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    });

    it('redirige al usuario ya autenticado que intenta ver el login', function () {
        $user = User::factory()->cliente()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect();
    });
});
