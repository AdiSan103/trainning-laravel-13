<?php

use App\Models\Login;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('halaman register dapat diakses tamu', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('Daftar Akun');
});

test('user baru dapat mendaftar', function () {
    $response = $this->post(route('register'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('login', ['email' => 'budi@example.com']);
    $this->assertAuthenticated();
});

test('validasi pendaftaran: email wajib', function () {
    $response = $this->post(route('register'), [
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('validasi pendaftaran: email harus unik', function () {
    Login::factory()->create(['email' => 'budi@example.com']);

    $response = $this->post(route('register'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('validasi pendaftaran: password minimal 8 karakter', function () {
    $response = $this->post(route('register'), [
        'email' => 'budi@example.com',
        'password' => '1234567',
        'password_confirmation' => '1234567',
    ]);

    $response->assertSessionHasErrors('password');
});

test('validasi pendaftaran: password harus dikonfirmasi', function () {
    $response = $this->post(route('register'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'berbeda',
    ]);

    $response->assertSessionHasErrors('password');
});

test('halaman login dapat diakses tamu', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Login');
});

test('user dapat login dengan kredensial benar', function () {
    $login = Login::factory()->create(['password' => 'password123']);

    $response = $this->post(route('login'), [
        'email' => $login->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertAuthenticated();
});

test('user gagal login dengan password salah', function () {
    $login = Login::factory()->create(['password' => 'password123']);

    $response = $this->post(route('login'), [
        'email' => $login->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user yang sudah login tidak bisa akses halaman register', function () {
    $login = Login::factory()->create();
    $this->actingAs($login);

    $response = $this->get(route('register'));

    $response->assertRedirect(route('posts.index'));
});

test('user yang sudah login tidak bisa akses halaman login', function () {
    $login = Login::factory()->create();
    $this->actingAs($login);

    $response = $this->get(route('login'));

    $response->assertRedirect(route('posts.index'));
});

test('halaman profile hanya bisa diakses user login', function () {
    $login = Login::factory()->create();
    $this->actingAs($login);

    $response = $this->get(route('profile.edit'));

    $response->assertStatus(200);
    $response->assertSee($login->email);
});

test('halaman profile tidak bisa diakses tamu', function () {
    $response = $this->get(route('profile.edit'));

    $response->assertRedirect(route('login'));
});

test('user dapat mengupdate email', function () {
    $login = Login::factory()->create();
    $this->actingAs($login);

    $response = $this->put(route('profile.update'), [
        'email' => 'baru@example.com',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('login', [
        'id_login' => $login->id_login,
        'email' => 'baru@example.com',
    ]);
});

test('user dapat mengganti password', function () {
    $login = Login::factory()->create(['password' => 'passwordlama']);
    $this->actingAs($login);

    $response = $this->put(route('profile.password'), [
        'current_password' => 'passwordlama',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ]);

    $response->assertRedirect(route('profile.edit'));

    Auth::logout();
    $this->assertTrue(Auth::attempt(['email' => $login->email, 'password' => 'passwordbaru']));
});

test('ganti password gagal jika current password salah', function () {
    $login = Login::factory()->create(['password' => 'passwordlama']);
    $this->actingAs($login);

    $response = $this->put(route('profile.password'), [
        'current_password' => 'passwordsalah',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ]);

    $response->assertSessionHasErrors('current_password');
});

test('user dapat logout', function () {
    $login = Login::factory()->create();
    $this->actingAs($login);
    $this->assertAuthenticated();

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('posts.index'));
    $this->assertGuest();
});
