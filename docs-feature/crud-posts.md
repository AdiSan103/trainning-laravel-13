# Belajar Laravel: Membuat CRUD Sederhana (Posts)

## 📋 Daftar Isi

1. [Apa yang Dibuat](#apa-yang-dibuat)
2. [Struktur CRUD di Laravel](#struktur-crud-di-laravel)
3. [Langkah 1: Membuat Migration & Model](#langkah-1-membuat-migration--model)
4. [Langkah 2: Membuat Controller](#langkah-2-membuat-controller)
5. [Langkah 3: Membuat View (Blade)](#langkah-3-membuat-view-blade)
6. [Langkah 4: Mendaftarkan Routes](#langkah-4-mendaftarkan-routes)
7. [Langkah 5: Menjalankan Migration & Testing](#langkah-5-menjalankan-migration--testing)
8. [Langkah 6: Testing dengan Pest](#langkah-6-testing-dengan-pest)
9. [Langkah 7: Menambahkan Upload Gambar](#langkah-7-menambahkan-upload-gambar)
10. [Kesimpulan & File yang Dibuat](#kesimpulan--file-yang-dibuat)

---

## Apa yang Dibuat

Kita membuat fitur **CRUD (Create, Read, Update, Delete)** untuk entitas **Post** — seperti blog sederhana. Pengguna bisa:

- Melihat **daftar** semua post (`index`)
- Melihat **detail** satu post (`show`)
- **Membuat** post baru (`create` + `store`)
- **Mengedit** post yang sudah ada (`edit` + `update`)
- **Menghapus** post (`destroy`)
- **Upload gambar** pada setiap post (opsional)

---

## Struktur CRUD di Laravel

Laravel mengikuti pola **MVC (Model-View-Controller)**:

```
Database  ⟷  Model  ⟷  Controller  ⟷  View (Blade)
                               ↕
                            Routes (web.php)
```

| Komponen | Fungsi |
|---|---|
| **Migration** | Membuat tabel di database (`title`, `body`) |
| **Model** | Menghubungkan kode PHP ke tabel `posts` |
| **Controller** | Logika bisnis: validasi, simpan, hapus, dll |
| **View (Blade)** | Tampilan HTML yang dilihat user |
| **Routes** | Menghubungkan URL ke Controller |

---

## Langkah 1: Membuat Migration & Model

### Perintah Artisan

```bash
php artisan make:model Post --migration --factory --no-interaction
```

Perintah ini membuat **3 file sekaligus**:
- `app/Models/Post.php` — Model
- `database/migrations/xxxx_create_posts_table.php` — Migration (tabel)
- `database/factories/PostFactory.php` — Factory (data dummy untuk testing)

### Isi Migration

Migration menentukan struktur tabel di database:

```php
// database/migrations/xxxx_create_posts_table.php
Schema::create('posts', function (Blueprint $table) {
    $table->id();            // Primary key, auto-increment
    $table->string('title'); // Kolom judul (VARCHAR 255)
    $table->text('body');    // Kolom isi (TEXT)
    $table->string('image')->nullable(); // Kolom path gambar (opsional)
    $table->timestamps();    // created_at & updated_at
});
```

> **Penjelasan**: `string('title')` artinya kolom VARCHAR(255), `text('body')` artinya TEXT (bisa menampung teks panjang). `nullable()` artinya kolom boleh kosong (NULL).

### Isi Model

```php
// app/Models/Post.php
class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'image'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
```

> **Penjelasan `$fillable`**: Properti ini adalah daftar kolom yang **boleh diisi massal** (mass assignment). Tanpa `$fillable`, `Post::create(['title' => '...'])` akan error karena Laravel melindungi dari mass assignment vulnerability.

### Isi Factory

```php
// database/factories/PostFactory.php
public function definition(): array
{
    return [
        'title' => fake()->sentence(),
        'body' => fake()->paragraphs(3, true),
    ];
}
```

> **Penjelasan Factory**: Factory digunakan untuk membuat data **palsu/dummy** saat testing. `fake()` adalah Faker library bawaan Laravel — `sentence()` menghasilkan kalimat acak, `paragraphs(3, true)` menghasilkan 3 paragraf jadi string.

---

## Langkah 2: Membuat Controller

### Perintah Artisan

```bash
php artisan make:controller PostController --resource --no-interaction
```

Flag `--resource` membuat controller dengan **7 method standar CRUD**:

| Method | URL | Fungsi |
|---|---|---|
| `index()` | GET `/posts` | Menampilkan semua post |
| `create()` | GET `/posts/create` | Form buat post baru |
| `store()` | POST `/posts` | Simpan post baru |
| `show()` | GET `/posts/{post}` | Detail satu post |
| `edit()` | GET `/posts/{post}/edit` | Form edit post |
| `update()` | PUT/PATCH `/posts/{post}` | Update post |
| `destroy()` | DELETE `/posts/{post}` | Hapus post |

### Konsep Penting di Controller

#### 1. Route Model Binding
```php
public function show(Post $post): View
```
Laravel otomatis mencari post berdasarkan ID dari URL dan meng-inject objek `Post`-nya. Tidak perlu manual `Post::find($id)`.

#### 2. Validasi Input
```php
$validated = $request->validate([
    'title' => ['required', 'string', 'max:255'],
    'body' => ['required', 'string'],
]);
```
`validate()` otomatis mengembalikan user ke form dengan pesan error jika validasi gagal.

#### 3. Redirect dengan Flash Message
```php
return redirect()->route('posts.index')
    ->with('success', 'Post berhasil dibuat.');
```
`with('success', ...)` menyimpan pesan ke **session** yang bisa ditampilkan di view.

#### 4. Pagination
```php
$posts = Post::latest()->paginate(10);
```
`paginate(10)` menampilkan 10 post per halaman. `latest()` mengurutkan dari yang terbaru.

---

## Langkah 3: Membuat View (Blade)

Blade adalah **template engine** Laravel. File disimpan di `resources/views/`.

### Struktur File yang Dibuat

```
resources/views/
├── layouts/
│   └── app.blade.php        ← Layout utama (navbar, flash message)
└── posts/
    ├── index.blade.php      ← Daftar semua post
    ├── create.blade.php     ← Form buat post
    ├── show.blade.php       ← Detail post
    └── edit.blade.php       ← Form edit post
```

### Konsep Blade yang Digunakan

#### 1. Template Inheritance (`@extends`)
```blade
@extends('layouts.app')   {{-- Pakai layout app --}}
@section('title', 'Judul') {{-- Isi section title --}}
@section('content')        {{-- Isi section content --}}
...
@endsection
```

#### 2. CSRF Protection (`@csrf`)
```blade
<form method="POST" action="...">
    @csrf    {{-- Wajib untuk POST/PUT/DELETE --}}
```
Setiap form POST/PUT/DELETE **harus** menyertakan `@csrf`. Laravel mengecek token ini untuk mencegah serangan CSRF.

#### 3. Method Spoofing (`@method`)
```blade
@method('PUT')     {{-- HTML form hanya support GET & POST, ini trik untuk PUT --}}
@method('DELETE')  {{-- Sama, untuk DELETE --}}
```

#### 4. Flash Message
```blade
@if (session('success'))
    <div class="bg-green-100 ...">{{ session('success') }}</div>
@endif
```

#### 5. Error Validation
```blade
@error('title')
    <p class="text-red-500">{{ $message }}</p>
@enderror
```

#### 6. Old Input
```blade
value="{{ old('title', $post->title) }}"
```
`old()` menampilkan input sebelumnya jika validasi gagal, sehingga user tidak perlu mengisi ulang.

#### 7. Pagination Links
```blade
{{ $posts->links() }}
```

---

## Langkah 4: Mendaftarkan Routes

```php
// routes/web.php
Route::get('/', [PostController::class, 'index']);
Route::resource('posts', PostController::class);
```

> **Penjelasan `Route::resource()`**: Satu baris ini otomatis membuat **7 route** untuk semua operasi CRUD. Cek dengan:

```bash
php artisan route:list
```

---

## Langkah 5: Menjalankan Migration & Testing

```bash
# Jalankan migration (membuat tabel di database)
php artisan migrate

# Format kode sesuai standar Laravel
php vendor/bin/pint --format agent

# Jalankan semua test
php artisan test --compact
```

---

## Langkah 6: Testing dengan Pest

Project ini menggunakan **Pest** (bukan PHPUnit) untuk testing.

```bash
php artisan make:test --pest PostTest --no-interaction
```

### Isi Test

Kita menulis **9 test** yang mencakup semua operasi CRUD:

```php
test('post baru dapat disimpan', function () {
    $this->post(route('posts.store'), [
        'title' => 'Judul',
        'body' => 'Isi post.',
    ]);

    $this->assertDatabaseHas('posts', ['title' => 'Judul']);
});
```

> **Catatan CSRF**: Di environment testing, Laravel biasanya menonaktifkan CSRF. Tapi di setup ini, kita perlu menambah `$this->withoutMiddleware(PreventRequestForgery::class)` di `beforeEach()` karena konfigurasi spesifik project.

### Assertions yang Digunakan

| Assertion | Fungsi |
|---|---|
| `assertStatus(200)` | Cek halaman berhasil dimuat |
| `assertSee($text)` | Cek teks muncul di halaman |
| `assertRedirect(...)` | Cek redirect setelah aksi |
| `assertDatabaseHas(...)` | Cek data ada di database |
| `assertDatabaseMissing(...)` | Cek data tidak ada di database |
| `assertSessionHasErrors(...)` | Cek validasi gagal |

---

## Langkah 7: Menambahkan Upload Gambar

### Konsep Dasar File Upload di Laravel

Laravel menyediakan **Filesystem** untuk menyimpan file. Kita akan menggunakan disk `public` yang menyimpan file di `storage/app/public/` dan bisa diakses via URL `/storage/...`.

**Storage Link**: Jalankan `php artisan storage:link` untuk membuat symlink dari `public/storage` ke `storage/app/public/`.

### 1. Menambah Kolom `image` di Migration

Kolom gambar bertipe `string` (menyimpan path file) dan `nullable()` karena gambar opsional:

```php
$table->string('image')->nullable();
```

### 2. Menambah `image` ke `$fillable` Model

```php
protected $fillable = ['title', 'body', 'image'];
```

### 3. Membuat Accessor `image_url`

Accessor memudahkan mendapatkan URL lengkap gambar:

```php
public function getImageUrlAttribute(): ?string
{
    return $this->image ? asset('storage/' . $this->image) : null;
}
```

Gunakan `$post->image_url` di Blade untuk mendapatkan URL gambar.

### 4. Menangani Upload di Controller

#### Validasi Gambar

```php
'image' => ['nullable', 'image', 'max:2048'],
```

| Aturan | Penjelasan |
|---|---|
| `nullable` | Field tidak wajib diisi |
| `image` | Harus file gambar (jpg, png, gif, webp, dll) |
| `max:2048` | Maksimal 2MB (dalam kilobyte) |

#### Store: Menyimpan File

```php
if ($request->hasFile('image')) {
    $validated['image'] = $request->file('image')->store('posts', 'public');
}
```

`store('posts', 'public')` menyimpan file ke `storage/app/public/posts/` dan mengembalikan path relatif seperti `posts/abc123.jpg`.

#### Update: Menghapus Gambar Lama

```php
if ($request->hasFile('image')) {
    if ($post->image) {
        Storage::disk('public')->delete($post->image);
    }
    $validated['image'] = $request->file('image')->store('posts', 'public');
}
```

#### Destroy: Membersihkan File

```php
if ($post->image) {
    Storage::disk('public')->delete($post->image);
}
```

### 5. Menampilkan Gambar di View

#### Index (Thumbnail)

```blade
@if ($post->image_url)
    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-24 h-24 object-cover rounded">
@endif
```

#### Show (Full Size)

```blade
@if ($post->image_url)
    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full max-w-md rounded mb-4">
@endif
```

#### Edit (Preview Gambar Lama)

```blade
@if ($post->image_url)
    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-48 rounded mb-2">
@endif
```

### 6. Form Upload

```blade
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" accept="image/*">
</form>
```

> **Penting**: Jangan lupa `enctype="multipart/form-data"` di form! Tanpa ini, file tidak akan terkirim.

### 7. Testing Upload Gambar dengan Pest

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('post baru dapat disimpan dengan gambar', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('foto.jpg', 800, 600);

    $this->post(route('posts.store'), [
        'title' => 'Post Dengan Gambar',
        'body' => 'Isi.',
        'image' => $image,
    ]);

    Storage::disk('public')->assertExists(
        Post::first()->image
    );
});
```

> **Penjelasan**: `Storage::fake('public')` membuat disk palsu di memory — tidak benar-benar menulis ke filesystem. `UploadedFile::fake()->image()` membuat file gambar dummy untuk testing.

Test untuk update gambar:

```php
test('post dapat diperbarui dengan gambar baru', function () {
    Storage::fake('public');
    $oldImage = UploadedFile::fake()->image('lama.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['image' => $oldImage]);

    $newImage = UploadedFile::fake()->image('baru.jpg');
    $this->put(route('posts.update', $post), [
        'title' => 'Baru',
        'body' => 'Isi.',
        'image' => $newImage,
    ]);

    $post->refresh();
    Storage::disk('public')->assertExists($post->image);
    Storage::disk('public')->assertMissing($oldImage);
});
```

### Ringkasan Langkah 7

```
1. Tambah kolom image (nullable string) di migration
2. Tambah 'image' ke $fillable model + accessor image_url
3. Validasi: 'nullable', 'image', 'max:2048'
4. Store file: $request->file('image')->store('posts', 'public')
5. Hapus file lama saat update atau destroy
6. Tambah enctype="multipart/form-data" di form
7. Tampilkan gambar dengan $post->image_url
8. Test: Storage::fake() + UploadedFile::fake()->image()
```

---

## Kesimpulan & File yang Dibuat

### Alur CRUD Laravel (Ringkasan)

```
1. php artisan make:model Post -m -f   → Buat Model + Migration + Factory
2. Edit migration: tambah kolom         → Tentukan struktur tabel
3. Edit model: tambah $fillable         → Tentukan kolom yang bisa diisi
4. php artisan make:controller -r       → Buat Controller resource
5. Isi logic di 7 method controller     → Implementasi CRUD
6. Buat view di resources/views/posts/  → Tampilan HTML
7. Daftarkan route di web.php           → Hubungkan URL ke Controller
8. php artisan migrate                  → Buat tabel di database
9. php artisan storage:link             → Symlink storage ke public
10. php artisan test                    → Pastikan semua berfungsi
```

### File yang Dibuat/Diubah

| File | Status |
|---|---|
| `database/migrations/xxxx_create_posts_table.php` | Dibuat |
| `app/Models/Post.php` | Dibuat & diedit |
| `database/factories/PostFactory.php` | Dibuat & diedit |
| `app/Http/Controllers/PostController.php` | Dibuat & diisi |
| `resources/views/layouts/app.blade.php` | Dibuat |
| `resources/views/posts/index.blade.php` | Dibuat |
| `resources/views/posts/create.blade.php` | Dibuat |
| `resources/views/posts/show.blade.php` | Dibuat |
| `resources/views/posts/edit.blade.php` | Dibuat |
| `routes/web.php` | Diubah |
| `tests/Feature/PostTest.php` | Dibuat & diisi |

### Hasil Test

```
Tests:  15 passed (38 assertions)
Duration: 4.06s
```

✅ Semua test **PASSED** — CRUD + upload gambar berfungsi dengan benar!

---

## Tips untuk Pemula

1. **Gunakan `php artisan make:`** — Jangan buat file manual. Artisan membuatkan struktur dasar yang sudah mengikuti konvensi Laravel.

2. **Pahami MVC flow**: Request masuk → Route → Controller → Model (database) → Controller → View → Response ke browser.

3. **Baca error message**: Laravel memberikan pesan error yang jelas. 419 = CSRF (lupa `@csrf`), 404 = route tidak ditemukan.

4. **Selalu tulis test**: Test memastikan kode kamu berfungsi sebelum dideploy.

5. **Gunakan `php artisan route:list`** untuk melihat semua route yang terdaftar.

6. **Gunakan `php artisan tinker`** untuk eksperimen cepat dengan kode PHP dan database.
