# 📝 Belajar Laravel — Blog CRUD + Authentication

Aplikasi blog sederhana untuk belajar Laravel, mencakup fitur CRUD Post (dengan upload gambar) dan sistem authentication lengkap (register, login, edit profil, ganti password, logout).

---

## 🧠 What is This Project

Aplikasi ini dibangun sebagai latihan memahami konsep-konsep fundamental Laravel:

- **MVC (Model-View-Controller)** — struktur inti framework
- **Eloquent ORM** — database query builder + relationship
- **Blade Template Engine** — layout inheritance, components, directives
- **Validation** — server-side input validation rules
- **File Upload** — storage disk, upload handling
- **Authentication** — session-based guard, middleware, password hashing
- **Testing (Pest)** — feature test dengan `RefreshDatabase`, `Storage::fake()`, `UploadedFile::fake()`

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | **PHP 8.3**, **Laravel 13** |
| Database | **SQLite** (dev/test), **MySQL** (production) |
| Frontend | **Blade**, **Tailwind CSS v4** (CDN) |
| Testing | **Pest v4** |
| Formatter | **Laravel Pint** |
| Build | **Vite** |
| AI Tools | **Laravel Boost**, **Claude Code Skills** |

## 📦 Installed Packages

| Package | Versi | Fungsi |
|---|---|---|
| `laravel/framework` | ^13.8 | Core framework |
| `laravel/tinker` | ^3.0 | Interactive REPL |
| `laravel/boost` | ^2.2 | AI coding agent tools |
| `pestphp/pest` | ^4.7 | Testing framework |
| `laravel/pint` | ^1.27 | Code formatter |
| `fakerphp/faker` | ^1.23 | Dummy data generator |

## 🗺️ Route List

| URL | Method | Middleware | Nama Route | Handler |
|---|---|---|---|---|
| `/` | GET | — | — | `PostController@index` |
| `/posts` | GET | — | `posts.index` | `PostController@index` |
| `/posts/create` | GET | — | `posts.create` | `PostController@create` |
| `/posts` | POST | — | `posts.store` | `PostController@store` |
| `/posts/{post}` | GET | — | `posts.show` | `PostController@show` |
| `/posts/{post}/edit` | GET | — | `posts.edit` | `PostController@edit` |
| `/posts/{post}` | PUT/PATCH | — | `posts.update` | `PostController@update` |
| `/posts/{post}` | DELETE | — | `posts.destroy` | `PostController@destroy` |
| `/register` | GET | `guest` | `register` | `AuthController@showRegister` |
| `/register` | POST | `guest` | — | `AuthController@register` |
| `/login` | GET | `guest` | `login` | `AuthController@showLogin` |
| `/login` | POST | `guest` | — | `AuthController@login` |
| `/profile` | GET | `auth` | `profile.edit` | `AuthController@showProfile` |
| `/profile` | PUT | `auth` | `profile.update` | `AuthController@updateProfile` |
| `/profile/password` | PUT | `auth` | `profile.password` | `AuthController@updatePassword` |
| `/logout` | POST | `auth` | `logout` | `AuthController@logout` |

## 📁 Project Structure (File Penting)

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php      ← Register, login, profile, logout
│       ├── PostController.php      ← CRUD Post + upload gambar
│       └── Controller.php          ← Base controller
├── Models/
│   ├── Post.php                    ← fillable: title, body, image + accessor image_url
│   └── User.php                    ← Authenticatable + auto-hash password
└── Providers/
    └── AppServiceProvider.php

database/
├── factories/
│   ├── PostFactory.php             ← Data dummy untuk testing
│   └── UserFactory.php
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   └── 2026_07_24_061333_create_posts_table.php  ← title, body, image(nullable)
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── layouts/
│   └── app.blade.php               ← Layout utama + navbar (auth/guest)
├── auth/
│   ├── login.blade.php
│   ├── profile.blade.php           ← Edit nama/email + ganti password
│   └── register.blade.php
└── posts/
    ├── create.blade.php            ← Form + upload gambar
    ├── edit.blade.php              ← Form + preview gambar + upload baru
    ├── index.blade.php             ← Daftar post + thumbnail gambar
    └── show.blade.php              ← Detail post + gambar full

routes/
├── web.php                         ← Semua route (posts + auth)
└── console.php

tests/
├── Feature/
│   ├── AuthTest.php                ← 18 test (register, login, profile, logout)
│   ├── PostTest.php                ← 15 test (CRUD + upload gambar)
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
├── TestCase.php
└── Pest.php

docs-feature/
└── authentication.md               ← Dokumentasi lengkap fitur auth

process-learn.md                    ← Panduan belajar CRUD Laravel (Bahasa Indonesia)
```

## 🚀 Cara Menjalankan

```bash
# Install dependencies
composer install
npm install

# Copy .env
cp .env.example .env

# Generate app key
php artisan key:generate

# Jalankan migration
php artisan migrate

# Storage symlink (untuk upload gambar)
php artisan storage:link

# Jalankan dev server
composer run dev
```

## ✅ Testing

```bash
# Jalankan semua test
php artisan test --compact

# Filter test spesifik
php artisan test --compact --filter="post baru dapat disimpan"

# Format kode
php vendor/bin/pint
```

**Hasil Terakhir:** Tests: 33 passed (82 assertions)
