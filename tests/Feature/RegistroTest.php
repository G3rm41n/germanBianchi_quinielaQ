<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Registro de usuarios', function () {

    it('puede ver el formulario de registro', function () {
        $this->get(route('register'))
            ->assertStatus(200)
            ->assertSee('Crear cuenta');
    });

    it('puede registrarse con datos válidos', function () {
        $this->post(route('register.store'), [
            'name' => 'Juan Pérez',
            'dni' => '12345678',
            'email' => 'juan@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])
            ->assertRedirect(route('jugadas.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'juan@test.com',
            'dni' => '12345678',
            'rol' => 'cliente',
        ]);

        $this->assertAuthenticated();
    });

    it('asigna rol cliente por defecto al registrarse', function () {
        $this->post(route('register.store'), [
            'name' => 'María García',
            'dni' => '87654321',
            'email' => 'maria@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        expect(User::where('email', 'maria@test.com')->first()->rol)->toBe('cliente');
    });

    it('no puede registrarse con DNI duplicado', function () {
        User::factory()->create(['dni' => '99999999']);

        $this->post(route('register.store'), [
            'name' => 'Otro Usuario',
            'dni' => '99999999',
            'email' => 'otro@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])
            ->assertSessionHasErrors('dni');
    });

    it('no puede registrarse con email duplicado', function () {
        User::factory()->create(['email' => 'duplicado@test.com']);

        $this->post(route('register.store'), [
            'name' => 'Otro Usuario',
            'dni' => '11111111',
            'email' => 'duplicado@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])
            ->assertSessionHasErrors('email');
    });

    it('no puede registrarse con contraseña menor a 8 caracteres', function () {
        $this->post(route('register.store'), [
            'name' => 'Usuario Test',
            'dni' => '22222222',
            'email' => 'test@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertSessionHasErrors('password');
    });

    it('no puede registrarse si las contraseñas no coinciden', function () {
        $this->post(route('register.store'), [
            'name' => 'Usuario Test',
            'dni' => '33333333',
            'email' => 'test2@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'OtraPassword456',
        ])
            ->assertSessionHasErrors('password');
    });

    it('redirige al usuario ya autenticado que intenta ver el registro', function () {
        $user = User::factory()->cliente()->create();

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect();
    });
});
