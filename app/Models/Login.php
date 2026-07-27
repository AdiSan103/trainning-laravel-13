<?php

// ============================================================
// NAMESPACE: Menentukan lokasi folder class ini (App\Models)
// ============================================================
namespace App\Models;

// === IMPORT CLASS BAWAAN LARAVEL ===
use Database\Factories\LoginFactory;                     // Factory untuk seeding data dummy Login
use Illuminate\Database\Eloquent\Attributes\Fillable;     // Attribute PHP 8 untuk kolom yg boleh diisi massal
use Illuminate\Database\Eloquent\Attributes\Hidden;       // Attribute PHP 8 untuk kolom yg disembunyikan dari JSON/array
use Illuminate\Database\Eloquent\Factories\HasFactory;    // Trait untuk menghubungkan model dengan Factory
use Illuminate\Foundation\Auth\User as Authenticatable;   // Class induk untuk model otentikasi user

// ============================================================
// CLASS Login — Model untuk tabel 'login'
// ============================================================
// Login extends Authenticatable (BUKAN Model biasa) karena class ini menangani
// otentikasi user (login, register, session). Authenticatable menyediakan
// method seperti Auth::attempt(), Auth::login(), dll.
//
// Attribute PHP 8 (#[NamaAttribute]) — cara modern Laravel 11+ untuk deklarasi
// properti model tanpa $fillable/$hidden di dalam class body.
// ============================================================

/**
 * #[Fillable(['email', 'password'])]
 * Menentukan kolom mana saja yang boleh diisi via mass assignment
 * (Post::create(), $model->update(), dll).
 * Kolom di luar daftar ini TIDAK akan terisi — ini fitur keamanan Laravel
 * untuk mencegah "mass assignment vulnerability".
 *
 * #[Hidden(['password'])]
 * Menentukan kolom yang otomatis disembunyikan ketika model di-serialize
 * ke array atau JSON (misalnya via API). Berguna untuk data sensitif.
 */
#[Fillable(['email', 'password'])]
#[Hidden(['password'])]
class Login extends Authenticatable
{
    /** @use HasFactory<LoginFactory> */
    // HasFactory — trait bawaan Laravel untuk membuat instance Factory
    // Berguna waktu seeding data atau testing: Login::factory()->count(10)->create()
    use HasFactory;

    // $table — memberitahu Laravel bahwa model ini terhubung ke tabel 'login'
    // Secara default Laravel otomatis pakai nama plural (logins), jadi ini override
    protected $table = 'login';

    // $primaryKey — memberitahu Laravel bahwa primary key tabel ini bernama 'id_login'
    // Default Laravel pakai 'id', jadi ini override karena tabel pakai nama berbeda
    protected $primaryKey = 'id_login';

    /**
     * casts() — Mengatur tipe data untuk kolom tertentu
     * Method ini otomatis dijalankan Laravel saat mengakses properti model.
     *
     * 'password' => 'hashed'
     * Memberi tahu Laravel bahwa kolom password sudah di-hash.
     * Efek: ketika kita set $login->password = 'rahasia', Laravel OTOMATIS
     * meng-hash password tersebut via bcrypt() sebelum disimpan ke DB.
     * Kita tidak perlu manual panggil Hash::make() atau bcrypt() lagi.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
