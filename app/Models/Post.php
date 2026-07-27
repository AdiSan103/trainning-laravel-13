<?php

// ============================================================
// NAMESPACE: Menentukan lokasi folder class ini (App\Models)
// ============================================================
namespace App\Models;

// === IMPORT CLASS BAWAAN LARAVEL ===
use Database\Factories\PostFactory;                      // Factory untuk seeding data dummy Post
use Illuminate\Database\Eloquent\Factories\HasFactory;   // Trait untuk menghubungkan model dengan Factory
use Illuminate\Database\Eloquent\Model;                  // Class induk untuk semua Eloquent Model

// ============================================================
// CLASS Post — Model untuk tabel 'post'
// ============================================================
// Post extends Model (class bawaan Laravel) yang menyediakan berbagai
// method untuk berinteraksi dengan database:
// - Post::all(), Post::find(), Post::create(), Post::where(), dll
// - Relasi: $post->relation()
// - Accessor/Mutator, Scope, Event, dll
// ============================================================
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    // HasFactory — trait bawaan Laravel untuk membuat instance Factory
    // Berguna waktu seeding: Post::factory()->count(10)->create()
    use HasFactory;

    // $table — memberitahu Laravel bahwa model ini terhubung ke tabel 'post'
    // Default Laravel otomatis mencari tabel plural (posts), jadi ini override
    protected $table = 'post';

    // $primaryKey — memberitahu Laravel bahwa primary key bernama 'id_post'
    // Default Laravel pakai 'id', jadi ini override
    protected $primaryKey = 'id_post';

    // $fillable — daftar kolom yang BOLEH diisi via mass assignment
    // (Post::create(), $post->update(), dll).
    // Kolom di luar array ini TIDAK akan terisi — ini untuk keamanan.
    // Bisa juga pakai $guarded (kebalikannya: "semua kecuali...").
    // Catatan: Login.php pakai #[Fillable] attribute (cara baru PHP 8),
    //          Post.php pakai $fillable property (cara lama/tradisional).
    //          Keduanya sama saja, beda gaya penulisan.
    protected $fillable = ['judul', 'tanggal', 'gambar', 'deskripsi'];

    /**
     * casts() — Mengatur tipe data untuk kolom tertentu
     * Otomatis dijalankan Laravel saat mengakses properti model.
     *
     * 'tanggal' => 'date'
     * Kolom 'tanggal' otomatis di-cast menjadi objek Carbon (library PHP untuk tanggal).
     * Efek: $post->tanggal->format('d M Y') bisa dipanggil langsung,
     * bukan return string biasa. Carbon menyediakan banyak helper tanggal.
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * getGambarUrlAttribute() — Accessor untuk kolom 'gambar_url'
     *
     * Accessor adalah method khusus Laravel dengan format:
     *     get[NamaKolom]Attribute()
     * yang membuat attribute VIRTUAL di model, seolah-olah itu kolom asli.
     *
     * Cara pakai di blade: $post->gambar_url
     * (Laravel otomatis panggil method ini)
     *
     * Fungsi: mengubah path gambar relatif menjadi URL absolut.
     * Contoh: "posts/abc.jpg" → "http://localhost/storage/posts/abc.jpg"
     *
     * asset() — helper Laravel yang bikin URL lengkap ke folder public/
     * storage/ — symlink ke storage/app/public/ (dibuat via php artisan storage:link)
     *
     * Return ?string — bisa null (nullable) kalau $this->gambar tidak ada
     */
    public function getGambarUrlAttribute(): ?string
    {
        // Jika $this->gambar ada, buat URL lengkap; jika null, return null
        return $this->gambar ? asset('storage/'.$this->gambar) : null;
    }
}
