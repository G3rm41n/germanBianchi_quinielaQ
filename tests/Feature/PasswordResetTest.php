<?php

use App\Models\User;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('puede ver la pantalla para solicitar restablecimiento de contrasena', function () {
    $this->get(route('password.request'))
        ->assertStatus(200)
        ->assertSee('Recuperar contraseña');
});

it('envia notificacion por correo con enlace seguro y redirige a la pantalla de aviso', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'cliente@test.com']);

    $response = $this->post(route('password.email'), [
        'email' => 'cliente@test.com',
    ]);

    $response->assertRedirect(route('password.sent'));
    $response->assertSessionHas('email_sent', 'cliente@test.com');

    Notification::assertSentTo(
        $user,
        CustomResetPasswordNotification::class,
        function ($notification) {
            expect($notification->token)->not->toBeEmpty();

            return true;
        }
    );
});

it('muestra la pantalla que indica revisar la bandeja de entrada', function () {
    $this->withSession(['email_sent' => 'juan@test.com'])
        ->get(route('password.sent'))
        ->assertStatus(200)
        ->assertSee('¡Revisa tu bandeja de entrada!')
        ->assertSee('juan@test.com');
});

it('falla al solicitar restablecimiento con correo no registrado', function () {
    Notification::fake();

    $response = $this->post(route('password.email'), [
        'email' => 'desconocido@test.com',
    ]);

    $response->assertSessionHasErrors('email');
    Notification::assertNothingSent();
});

it('puede visualizar la pantalla de ingreso de nueva contrasena', function () {
    $this->get(route('password.reset', [
        'token' => 'sample-token',
        'email' => 'cliente@test.com',
    ]))
        ->assertStatus(200)
        ->assertSee('Nueva contraseña');
});

it('puede cambiar la contrasena satisfactoriamente con token valido', function () {
    $user = User::factory()->create([
        'email' => 'cliente@test.com',
        'password' => Hash::make('ClaveAnterior123'),
    ]);

    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'cliente@test.com',
        'password' => 'NuevaClaveSegura999',
        'password_confirmation' => 'NuevaClaveSegura999',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect(Hash::check('NuevaClaveSegura999', $user->password))->toBeTrue();
});

it('rechaza cambio de contrasena si las confirmaciones no coinciden', function () {
    $user = User::factory()->create(['email' => 'cliente@test.com']);
    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'cliente@test.com',
        'password' => 'NuevaClave123',
        'password_confirmation' => 'DistintaClave456',
    ]);

    $response->assertSessionHasErrors('password');
});
