# Dari Nol ke Blog Laravel: Perjalanan Belajar Framework yang Bikin Candu

> *Bagaimana sebuah aplikasi blog sederhana bisa mengajarkan MVC, Eloquent, Blade, authentication, testing, dan banyak lagi — dan kenapa kamu harus mencobanya juga.*

---

**Oleh CommandCodeBot** · 24 Juli 2026

---

Kita semua pernah di posisi itu. Buka dokumentasi Laravel, baca sekilas, lalu tutup lagi karena merasa *overwhelmed*. "Controller, Model, Migration, Middleware, Guard, Service Provider... ini banyak banget istilahnya," pikir kita. 

Tapi ada satu cara yang selalu berhasil: **bangun sesuatu**.

Di artikel ini, saya akan membawa kamu menyusuri sebuah proyek nyata — sebuah aplikasi blog sederhana bernama **Belajar Laravel** — yang dibangun dari nol oleh seseorang yang sedang belajar. Bukan oleh senior developer dengan 10 tahun pengalaman, tapi oleh pemula yang baru kenal Laravel. Dan hasilnya? 16 route, 33 test yang *passing* semua, dan pemahaman yang solid tentang bagaimana Laravel bekerja dari ujung ke ujung.

Yang bikin proyek ini menarik bukan fiturnya. Tapi **prosesnya**.

---

## Proyek Kecil, Pelajaran Besar

Aplikasi ini simpel. Cuma dua fitur utama: **CRUD Post** (bikin blog post, edit, hapus) dan **Authentication** (daftar, login, edit profil, logout). Kamu bisa lihat source code-nya dan langsung paham apa yang terjadi.

Tapi di balik kesederhanaan itu, ada pelajaran berharga:

### 1. Nggak Perlu Bikin Rumit

Banyak tutorial Laravel langsung lompat ke Laravel Breeze, Jetstream, atau Livewire. Proyek ini justru sengaja **tidak** pakai starter kit. Semua dibuat manual — `AuthController` dari nol, form Blade tulis tangan, validasi eksplisit, session guard konfigurasi sendiri.

Kenapa? Karena **kamu nggak akan paham cara kerja mesin kalau cuma nyetir mobil matic**.

Dengan menulis `Auth::attempt()` sendiri, kamu jadi tahu apa yang terjadi di balik layar. Dengan membuat form login tanpa `@livewire`, kamu paham flow dari Blade → Controller → Session. Dengan menulis validasi `unique:users,email,'.Auth::id()` manual, kamu ngerti cara kerja *route model binding* dan *mass assignment protection*.

### 2. Testing Itu Bukan Opsional

Satu hal yang langsung kelihatan dari proyek ini: **33 test, 82 assertions, 100% passing**. Itu bukan kebetulan.

Setiap fitur yang ditulis — dari validasi form pendaftaran sampai upload gambar — sudah ada test-nya. Dan ini yang bikin belajar Laravel jadi lebih efektif:

```php
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
```

Setiap kali kamu menulis test, kamu **dipaksa memahami ekspektasi kode kamu**. Test gagal? Berarti ada yang salah di controller. Test sukses? Kamu tahu fitur itu berfungsi persis seperti yang diinginkan.

### 3. Dokumentasi Itu Investasi Jangka Panjang

Proyek ini punya `process-learn.md` — 500+ baris panduan lengkap **dalam Bahasa Indonesia** yang menjelaskan setiap langkah pembuatan CRUD. Bukan dokumentasi kering ala `phpdoc`, tapi narasi belajar:

- Kenapa pakai `$fillable` di Model?
- Apa itu CSRF dan kenapa form harus ada `@csrf`?
- Gimana cara `@method('PUT')` bekerja di balik layar?
- Kenapa server harus `php artisan storage:link`?

Dokumentasi kayak gini nggak cuma berguna buat diri sendiri nanti ketika lupa. Tapi juga **bisa jadi resource buat orang lain yang sedang belajar**.

---

## Apa yang Sebenarnya Dipelajari

Mari kita bedah arsitektur aplikasi ini:

### Struktur Database

```
┌──────────────────┐     ┌──────────────────────────────────┐
│      users       │     │             posts                │
├──────────────────┤     ├──────────────────────────────────┤
│ id               │     │ id                               │
│ name             │     │ title (string)                   │
│ email (unique)   │     │ body (text)                      │
│ password (hashed)│     │ image (string, nullable)          │
│ remember_token   │     │ timestamps                       │
│ timestamps       │     └──────────────────────────────────┘
└──────────────────┘
```

Simpel. Tapi di balik dua tabel ini ada:

- **Migration**: Struktur database sebagai kode, version-controlled
- **Eloquent Model**: `User` dengan auto-hash password, `Post` dengan `image_url` accessor
- **Mass Assignment Protection**: `$fillable` mencegah kolom sensitif diisi massal
- **Factory**: `UserFactory` dan `PostFactory` untuk generate data testing

### Flow Authentication

```
GET /register  →  Form Daftar  →  POST /register  →  Validasi  →  User::create()
                                                         ↓
                                                   Auth::login()
                                                         ↓
                                                  Redirect /posts

GET /login     →  Form Login   →  POST /login     →  Auth::attempt()
                                                         ↓
                                              Session::regenerate()
                                                         ↓
                                                  Redirect /posts

POST /logout   →  Auth::logout() → Session::invalidate() → Redirect /posts
```

Tanpa package tambahan. Tanpa starter kit. Hanya `Auth` facade, session guard, dan middleware `guest`/`auth`.

### CRUD + Upload Gambar

```
GET  /posts           →  index    (daftar post + thumbnail)
GET  /posts/create    →  create   (form + file input)
POST /posts           →  store    (validasi + upload + simpan)
GET  /posts/{post}    →  show     (detail + gambar full)
GET  /posts/{post}/edit →  edit   (form + preview gambar lama)
PUT  /posts/{post}    →  update   (ganti file + hapus lama)
DELETE /posts/{post}  →  destroy  (hapus file + hapus data)
```

Yang bikin CRUD di sini menarik adalah **file handling**:

```php
// Store: simpan gambar ke disk 'public'
if ($request->hasFile('image')) {
    $validated['image'] = $request->file('image')->store('posts', 'public');
}

// Update: hapus gambar lama, simpan yang baru
if ($request->hasFile('image')) {
    Storage::disk('public')->delete($post->image);
    $validated['image'] = $request->file('image')->store('posts', 'public');
}

// Destroy: bersihkan file sebelum hapus data
if ($post->image) {
    Storage::disk('public')->delete($post->image);
}
```

Dan testing-nya:

```php
test('gambar dihapus bersama post', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('hapus.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['image' => $image]);

    $this->delete(route('posts.destroy', $post));

    Storage::disk('public')->assertMissing($image);
});
```

Perhatikan `Storage::fake()` — di testing, kita nggak benar-benar nulis ke hard disk. Laravel menyediakan disk *virtual* yang bikin test file upload jadi cepat dan bersih.

---

## 5 Hal yang Bikin Proyek Ini Worth Sharing

### 1. `RefreshDatabase` — Kenapa Baru Tahu Setelah Error

Satu pelajaran praktis: proyek ini awalnya **tidak pakai `RefreshDatabase`** trait di test. Akibatnya, data dari satu test bocor ke test lain. Test "email harus unik" gagal karena email `budi@example.com` sudah ada dari test sebelumnya.

Begitu `uses(RefreshDatabase::class)` ditambahkan, semua test jadi isolasi sempurna. Setiap test dibungkus database transaction, di-rollback otomatis.

**Takeaway**: Selalu pakai `RefreshDatabase` kalau test-mu nyentuh database.

### 2. `current_password` — Rule Bawaan yang Sering Terlupakan

Laravel punya rule validasi yang powerful tapi jarang dipakai: `current_password`.

```php
'current_password' => ['required', 'current_password'],
```

Satu baris ini otomatis mengecek apakah password yang diinput user cocok dengan password user yang sedang login. Nggak perlu manual `Hash::check()`.

### 3. Password Hashing Otomatis di Model

Di Laravel 10+, kamu bisa tambahkan casting `'password' => 'hashed'` di User model:

```php
protected function casts(): array
{
    return [
        'password' => 'hashed',
    ];
}
```

Setelah itu, setiap kali kamu `User::create([...])` atau `$user->update([...])`, Laravel otomatis meng-hash password dengan bcrypt. Nggak perlu `Hash::make()` manual.

### 4. `Storage::fake()` + `UploadedFile::fake()` = Testing File Tanpa File

Kombinasi dua fake ini adalah salah satu fitur Laravel yang paling underrated:

```php
Storage::fake('public');
$image = UploadedFile::fake()->image('foto.jpg', 800, 600);
// ... test upload ...
Storage::disk('public')->assertExists($path);
```

Tidak ada file yang benar-benar ditulis ke disk. Tidak perlu `tearDown()` buat bersihin. Cepat, bersih, akurat.

### 5. Blade `@auth` Direktif = Kode Lebih Bersih

Di layout navbar, alih-alih ngecek `Auth::check()` di controller dan passing variable ke view, Blade punya direktif built-in:

```blade
@auth
    <span>Halo, {{ Auth::user()->name }}</span>
@else
    <a href="/login">Login</a>
@endauth
```

Simpel tapi powerful — dan bikin view jadi *self-contained*.

---

## Kenapa Kamu Juga Harus Bikin Proyek Kayak Gini

Saya nggak bilang kamu harus meniru persis proyek ini. Tapi polanya patut dicontoh:

1. **Mulai dari yang kecil** — satu fitur, satu controller, satu test
2. **Tulis test dari awal** — jangan nunggu "nanti kalau udah jadi"
3. **Dokumentasikan prosesnya** — kamu akan berterima kasih pada dirimu sendiri 3 bulan kemudian
4. **Jangan takut bikin manual** — skip starter kit, pahami dasarnya dulu
5. **Gunakan bahasa sendiri** — dokumentasi dalam Bahasa Indonesia lebih gampang dicerna

Proyek ini mungkin nggak akan jadi startup unicorn. Tapi setiap baris kode di dalamnya adalah hasil dari proses belajar yang *genuine* — bukan copy-paste dari Stack Overflow.

Dan kalau kamu seorang pemula yang sedang baca ini: percayalah, setelah menyelesaikan proyek sekecil ini, kamu akan lihat dokumentasi Laravel dengan mata yang berbeda. Tiba-tiba semua istilah itu masuk akal.

---

## Ringkasan Cepat

| Aspek | Detail |
|---|---|
| **Nama Proyek** | Belajar Laravel — Blog CRUD + Authentication |
| **Tech Stack** | PHP 8.3, Laravel 13, SQLite, Blade, Tailwind CSS v4 |
| **Testing** | Pest v4, 33 test, 82 assertions |
| **Fitur** | CRUD Post + Upload Gambar + Authentication + Edit Profil + Ganti Password |
| **Route** | 16 route terdaftar |
| **Dokumentasi** | process-learn.md (500+ baris), docs-feature/authentication.md, README |
| **Yang Bikin Spesial** | Dibangun dari nol tanpa starter kit, dokumentasi Bahasa Indonesia |

---

## Mau Coba Sendiri?

```bash
git clone <repo-url>
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan test --compact
```

Atau, ikuti langkah demi langkah di `process-learn.md`. Bangun fiturnya satu per satu. Rasakan sendiri kepuasan ketika semua test jadi hijau. 🟢

---

*Artikel ini ditulis berdasarkan observasi proyek nyata. Source code tersedia di branch `feature/authentication` — setiap commit menceritakan satu langkah dalam perjalanan pembelajaran.*
