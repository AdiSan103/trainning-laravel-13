# Fitur Authentication — Register, Login, Edit Profil, Logout

## 📋 Daftar Isi

1. [Apa yang Dibuat](#apa-yang-dibuat)
2. [Arsitektur Authentication Laravel](#arsitektur-authentication-laravel)
3. [Langkah 1: Membuat AuthController](#langkah-1-membuat-authcontroller)
4. [Langkah 2: Menambahkan Routes](#langkah-2-menambahkan-routes)
5. [Langkah 3: Membuat View (Blade)](#langkah-3-membuat-view-blade)
6. [Langkah 4: Mengupdate Layout Navbar](#langkah-4-mengupdate-layout-navbar)
7. [Langkah 5: Konfigurasi Middleware Redirect](#langkah-5-konfigurasi-middleware-redirect)
8. [Langkah 6: Testing Authentication](#langkah-6-testing-authentication)
9. [Kesimpulan & Flow Authentication](#kesimpulan--flow-authentication)

---

## Apa yang Dibuat

Fitur authentication lengkap untuk aplikasi Laravel berbasis session. Pengguna bisa:

- **Mendaftar** akun baru dengan validasi (nama, email unik, password min 8 karakter)
- **Login** dengan email & password, termasuk fitur "remember me"
- **Mengedit profil** — ubah nama dan email
- **Mengganti password** — dengan verifikasi password saat ini (`current_password`)
- **Logout** — menghapus session dan redirect ke halaman posts

### File yang Dibuat/Diubah

| File | Status |
|---|---|
| `app/Http/Controllers/AuthController.php` | Dibuat |
| `resources/views/auth/register.blade.php` | Dibuat |
| `resources/views/auth/login.blade.php` | Dibuat |
| `resources/views/auth/profile.blade.php` | Dibuat |
| `routes/web.php` | Diubah |
| `resources/views/layouts/app.blade.php` | Diubah |
| `bootstrap/app.php` | Diubah |
| `tests/Feature/AuthTest.php` | Dibuat |

---

## Arsitektur Authentication Laravel

Laravel authentication menggunakan pola **Guard + Provider**:

```
Browser Request → Session (cookie) → Auth Guard → User Provider → User Model
                                                      ↓
                                              users table (database)
```

| Komponen | Penjelasan |
|---|---|
| **Guard** (`web`) | Menentukan cara autentikasi — `session` guard membaca user ID dari session. |
| **Provider** (`users`) | Menentukan sumber data user — `eloquent` provider menggunakan User model. |
| **Middleware `auth`** | Melindungi route, hanya user yang sudah login yang bisa akses. |
| **Middleware `guest`** | Hanya user yang BELUM login yang bisa akses (login/register page). |

Konfigurasi ada di `config/auth.php` — default Laravel sudah benar, tidak perlu diubah.

---

## Langkah 1: Membuat AuthController

Semua logic authentication dikumpulkan dalam satu controller: `AuthController`.

### Struktur Method

| Method | Route | Middleware | Fungsi |
|---|---|---|---|
| `showRegister()` | GET `/register` | guest | Tampilkan form daftar |
| `register()` | POST `/register` | guest | Proses pendaftaran |
| `showLogin()` | GET `/login` | guest | Tampilkan form login |
| `login()` | POST `/login` | guest | Proses login |
| `showProfile()` | GET `/profile` | auth | Tampilkan form edit profil |
| `updateProfile()` | PUT `/profile` | auth | Update nama/email |
| `updatePassword()` | PUT `/profile/password` | auth | Ganti password |
| `logout()` | POST `/logout` | auth | Logout |

### Konsep Penting

#### 1. Validasi Register

```php
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
]);
```

| Aturan | Penjelasan |
|---|---|
| `unique:users` | Email tidak boleh sama dengan yang sudah ada di tabel `users` |
| `min:8` | Password minimal 8 karakter |
| `confirmed` | Harus ada field `password_confirmation` yang nilainya sama |

#### 2. Auto-Login Setelah Register

```php
$user = User::create($validated);
Auth::login($user);
```

User langsung login setelah berhasil mendaftar — tidak perlu login ulang.

#### 3. Password Hashing Otomatis

Model User memiliki **casting** `'password' => 'hashed'`:

```php
protected function casts(): array
{
    return [
        'password' => 'hashed',
    ];
}
```

Setiap kali password diset ke model (via `create()` atau `update()`), Laravel otomatis meng-hash dengan bcrypt. Tidak perlu manual `Hash::make()`.

#### 4. Attempt Login

```php
if (Auth::attempt($credentials, $request->filled('remember'))) {
    $request->session()->regenerate();
    return redirect()->intended(route('posts.index'));
}
```

- `Auth::attempt()` mencocokkan email + password, return `true` jika cocok.
- `session()->regenerate()` membuat session ID baru — mencegah **session fixation attack**.
- `remember` parameter kedua: jika checkbox dicentang, user tetap login meskipun browser ditutup.
- `redirect()->intended()` mengarahkan ke URL yang tadinya ingin diakses sebelum dipaksa login.

#### 5. Logout

```php
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```

- `session()->invalidate()` menghapus semua data session.
- `session()->regenerateToken()` membuat CSRF token baru.

#### 6. Edit Profil — Unique Email Kecuali User Sendiri

```php
'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
```

`unique:users,email,1` artinya: email harus unik di tabel users, **kecuali** untuk user dengan ID 1 (user yang sedang login). Jadi user bisa "mengubah" email ke email yang sama (tidak berubah).

#### 7. Ganti Password — Validasi `current_password`

```php
'current_password' => ['required', 'current_password'],
```

Rule `current_password` otomatis mengecek apakah password yang diinput cocok dengan password user yang sedang login. Jika tidak cocok, validasi gagal.

---

## Langkah 2: Menambahkan Routes

Semua route auth dikelompokkan dengan middleware:

```php
// Hanya bisa diakses tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Hanya bisa diakses user yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.edit');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
```

> **Perbedaan Method**: Profile dan password menggunakan `PUT` (update data), sedangkan logout menggunakan `POST` (aksi yang mengubah state session).

### Daftar Route Lengkap

| URL | Method | Middleware | Nama Route |
|---|---|---|---|
| `/register` | GET | guest | `register` |
| `/register` | POST | guest | — |
| `/login` | GET | guest | `login` |
| `/login` | POST | guest | — |
| `/profile` | GET | auth | `profile.edit` |
| `/profile` | PUT | auth | `profile.update` |
| `/profile/password` | PUT | auth | `profile.password` |
| `/logout` | POST | auth | `logout` |

---

## Langkah 3: Membuat View (Blade)

Semua view auth disimpan di `resources/views/auth/`:

```
resources/views/auth/
├── register.blade.php   ← Form pendaftaran (nama, email, password, konfirmasi)
├── login.blade.php      ← Form login (email, password, remember me)
└── profile.blade.php    ← Edit profil + ganti password (2 form terpisah)
```

### Konsep Blade yang Digunakan

#### Form Register

```blade
<form action="{{ route('register') }}" method="POST">
    @csrf
    <input name="name" value="{{ old('name') }}" required>
    <input name="email" type="email" value="{{ old('email') }}" required>
    <input name="password" type="password" required>
    <input name="password_confirmation" type="password" required>
</form>
```

`old('name')` menampilkan input sebelumnya jika validasi gagal.

#### Form Login

```blade
<input type="checkbox" name="remember">
<span>Ingat saya</span>
```

Checkbox `remember` dikirim ke `Auth::attempt()` untuk fitur "remember me".

#### Form Profile (2 form)

Form pertama: update nama + email (`PUT /profile`)
Form kedua: ganti password (`PUT /profile/password`)

```blade
<form action="{{ route('profile.update') }}" method="POST">
    @csrf
    @method('PUT')
    <input name="name" value="{{ old('name', Auth::user()->name) }}">
    <input name="email" value="{{ old('email', Auth::user()->email) }}">
</form>


<form action="{{ route('profile.password') }}" method="POST">
    @csrf
    @method('PUT')
    <input name="current_password" type="password">
    <input name="password" type="password">
    <input name="password_confirmation" type="password">
</form>
```

Menggunakan `Auth::user()` untuk pre-fill data user yang sedang login.

#### Method Spoofing

HTML form hanya support `GET` dan `POST`. Untuk mengirim `PUT`, gunakan:

```blade
@method('PUT')
```

Ini membuat hidden input `<input name="_method" value="PUT">` yang dibaca Laravel.

---

## Langkah 4: Mengupdate Layout Navbar

Navbar menampilkan link berbeda berdasarkan status login:

```blade
@auth
    {{-- User sudah login --}}
    <a href="{{ route('profile.edit') }}">👤 {{ Auth::user()->name }}</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
@else
    {{-- User belum login --}}
    <a href="{{ route('login') }}">Login</a>
    <a href="{{ route('register') }}">Daftar</a>
@endauth
```

| Direktif | Penjelasan |
|---|---|
| `@auth` | Kode di dalamnya hanya dirender jika user sudah login |
| `@else` | Kode di dalamnya dirender jika user belum login |
| `Auth::user()` | Mengembalikan instance User yang sedang login |

---

## Langkah 5: Konfigurasi Middleware Redirect

Di `bootstrap/app.php`, kita konfigurasi kemana redirect setelah login/logout:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->redirectGuestsTo(fn () => route('login'));
    $middleware->redirectUsersTo(fn () => route('posts.index'));
})
```

| Method | Fungsi |
|---|---|
| `redirectGuestsTo()` | Jika tamu mencoba akses route `auth`, redirect ke halaman login |
| `redirectUsersTo()` | Jika user login mencoba akses route `guest`, redirect ke posts index |

---

## Langkah 6: Testing Authentication

Testing menggunakan **Pest** dengan `RefreshDatabase` trait.

### Setup

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
```

`RefreshDatabase` membungkus setiap test dalam database transaction — data test di-rollback otomatis, database bersih kembali.

### Daftar Test (18 test)

#### Register (7 test)

| Test | Fungsi |
|---|---|
| Halaman register dapat diakses tamu | Cek GET `/register` return 200 dan berisi "Daftar Akun" |
| User baru dapat mendaftar | POST register → cek redirect, cek database, cek authenticated |
| Validasi: name wajib | POST tanpa name → assertSessionHasErrors('name') |
| Validasi: email wajib | POST tanpa email → assertSessionHasErrors('email') |
| Validasi: email harus unik | Buat user dulu, POST dengan email sama → error |
| Validasi: password min 8 | POST password 7 karakter → error |
| Validasi: password confirmation | POST password berbeda dengan confirmation → error |

#### Login (3 test)

| Test | Fungsi |
|---|---|
| Halaman login dapat diakses tamu | GET `/login` return 200 |
| User dapat login | POST login kredensial benar → redirect, authenticated |
| User gagal login | POST login password salah → error, tetap guest |

#### Middleware Protection (3 test)

| Test | Fungsi |
|---|---|
| User login tidak bisa akses register | `actingAs()` user, GET register → redirect |
| User login tidak bisa akses login | `actingAs()` user, GET login → redirect |
| Tamu tidak bisa akses profile | Tamu GET profile → redirect ke login |

#### Profile (3 test)

| Test | Fungsi |
|---|---|
| Profile bisa diakses user login | `actingAs()` → GET profile → assertSee nama |
| User dapat update nama & email | PUT profile → cek redirect + database |
| User dapat ganti password | PUT password bar + current password benar → cek login ulang |
| Ganti password gagal jika current password salah | PUT password salah → assertSessionHasErrors |

#### Logout (1 test)

| Test | Fungsi |
|---|---|
| User dapat logout | `actingAs()` → POST logout → redirect + assertGuest |

### Assertions Penting

```php
// Cek user sedang login
$this->assertAuthenticated();

// Cek user TIDAK sedang login (guest)
$this->assertGuest();

// Login sebagai user tertentu untuk test
$this->actingAs($user);

// Cek data di database
$this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
```

---

## Kesimpulan & Flow Authentication

### Flow Register

```
User → GET /register → Form daftar → POST /register
  → Validasi (name, email unique, password min:8 confirmed)
  → User::create() + auto-hash password
  → Auth::login() (auto-login)
  → Redirect /posts dengan flash message
```

### Flow Login

```
User → GET /login → Form login → POST /login
  → Auth::attempt(email, password)
  → Jika gagal: back() dengan error "Email atau password salah"
  → Jika sukses: session regenerate + redirect intended
```

### Flow Edit Profil

```
User → GET /profile → Form edit + form ganti password
  → PUT /profile → update nama/email → redirect /profile
  → PUT /profile/password → validasi current_password → update password → redirect /profile
```

### Flow Logout

```
User → POST /logout
  → Auth::logout()
  → Session invalidate + regenerate token
  → Redirect /posts
```

### Hasil Test

```
Tests:  33 passed (82 assertions)
Duration: 15.75s
```

✅ Semua test **PASSED** — authentication berfungsi dengan benar!

---

## Tips

1. **Password hashing**: Model User dengan cast `'hashed'` sudah otomatis, tidak perlu `Hash::make()` manual.

2. **Session regenerate**: Selalu panggil `$request->session()->regenerate()` setelah login untuk mencegah session fixation.

3. **Middleware grouping**: Kelompokkan route dengan middleware `guest` dan `auth` agar bersih.

4. **`unique:users,email,ID`**: Saat update profil, tambahkan ID user agar validasi unique mengabaikan user sendiri.

5. **`current_password` rule**: Gunakan rule ini untuk memverifikasi password lama sebelum mengganti password.

6. **`RefreshDatabase`**: Wajib digunakan di test agar data antar test tidak saling mengganggu.
