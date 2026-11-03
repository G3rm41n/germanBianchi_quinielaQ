<?php

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('envia notificacion de verificacion por correo al registrarse', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Gonzalo Higuaín',
        'dni' => '32111222',
        'email' => 'gonzalo@test.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $user = User::where('email', 'gonzalo@test.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
});

it('muestra la pantalla de aviso de verificacion a usuarios no verificados', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertStatus(200)
        ->assertSee('Verificá tu correo');
});

it('permite verificar la cuenta exitosamente con enlace firmado valido', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect(route('jugadas.index'));
    $response->assertSessionHas('success');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rechaza la verificacion con hash alterado o invalido', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $badUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('correo_invalido@test.com')]
    );

    $this->actingAs($user)->get($badUrl)->assertStatus(403);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('permite reenviar el correo de verificacion', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
});
