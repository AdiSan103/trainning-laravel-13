<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    $this->assertAuthenticated();
});

test('validasi pendaftaran: name wajib', function () {
    $response = $this->post(route('register'), [
        'name' => '',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('name');
});

test('validasi pendaftaran: email wajib', function () {
    $response = $this->post(route('register'), [
        'name' => 'Budi',
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('validasi pendaftaran: email harus unik', function () {
    User::factory()->create(['email' => 'budi@example.com']);

    $response = $this->post(route('register'), [
        'name' => 'Budi Lain',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('validasi pendaftaran: password minimal 8 karakter', function () {
    $response = $this->post(route('register'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'password' => '1234567',
        'password_confirmation' => '1234567',
    ]);

    $response->assertSessionHasErrors('password');
});

test('validasi pendaftaran: password harus dikonfirmasi', function () {
    $response = $this->post(route('register'), [
        'name' => 'Budi',
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
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertAuthenticated();
});

test('user gagal login dengan password salah', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user yang sudah login tidak bisa akses halaman register', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('register'));

    $response->assertRedirect(route('posts.index'));
});

test('user yang sudah login tidak bisa akses halaman login', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('login'));

    $response->assertRedirect(route('posts.index'));
});

test('halaman profile hanya bisa diakses user login', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('profile.edit'));

    $response->assertStatus(200);
    $response->assertSee($user->name);
});

test('halaman profile tidak bisa diakses tamu', function () {
    $response = $this->get(route('profile.edit'));

    $response->assertRedirect(route('login'));
});

test('user dapat mengupdate nama dan email', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->put(route('profile.update'), [
        'name' => 'Nama Baru',
        'email' => 'baru@example.com',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nama Baru',
        'email' => 'baru@example.com',
    ]);
});

test('user dapat mengganti password', function () {
    $user = User::factory()->create(['password' => 'passwordlama']);
    $this->actingAs($user);

    $response = $this->put(route('profile.password'), [
        'current_password' => 'passwordlama',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ]);

    $response->assertRedirect(route('profile.edit'));

    Auth::logout();
    $this->assertTrue(Auth::attempt(['email' => $user->email, 'password' => 'passwordbaru']));
});

test('ganti password gagal jika current password salah', function () {
    $user = User::factory()->create(['password' => 'passwordlama']);
    $this->actingAs($user);

    $response = $this->put(route('profile.password'), [
        'current_password' => 'passwordsalah',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ]);

    $response->assertSessionHasErrors('current_password');
});

test('user dapat logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->assertAuthenticated();

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('posts.index'));
    $this->assertGuest();
});
