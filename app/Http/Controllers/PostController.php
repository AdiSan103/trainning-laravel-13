<?php

// ============================================================
// NAMESPACE: Menentukan lokasi folder class ini (App\Http\Controllers)
// ============================================================
namespace App\Http\Controllers;

// === IMPORT MODEL & CLASS BAWAAN LARAVEL ===
use App\Models\Post;                             // Model Post untuk query tabel posts
use Illuminate\Http\RedirectResponse;            // Tipe return untuk redirect
use Illuminate\Http\Request;                     // Class untuk menangani data request HTTP
use Illuminate\Support\Facades\Storage;          // Facade Storage untuk manajemen file
use Illuminate\View\View;                        // Tipe return untuk view/blade

// ============================================================
// CLASS PostController — Mengatur semua fungsi CRUD postingan
// ============================================================
class PostController extends Controller
{
    /**
     * index() — Menampilkan daftar semua postingan (halaman utama)
     * Method: GET /posts
     * Return: View dengan data posts
     *
     * Alur:
     * 1. Query semua data post, diurutkan dari yg terbaru (latest())
     * 2. Paginate 10 data per halaman
     * 3. Kirim data ke view posts.index
     */
    public function index(): View
    {
        // Post::latest() — ambil semua data diurutkan dari created_at terbaru
        // ->paginate(10) — bagi data per halaman, masing2 10 item
        $posts = Post::latest()->paginate(10);

        // compact('posts') — cara singkat nulis ['posts' => $posts]
        // Data dikirim ke blade supaya bisa dipake di foreach
        return view('posts.index', compact('posts'));
    }

    /**
     * create() — Menampilkan form untuk membuat postingan baru
     * Method: GET /posts/create
     * Return: View (form create)
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * store() — Menyimpan postingan baru ke database
     * Method: POST /posts
     * Return: RedirectResponse
     *
     * Alur:
     * 1. Validasi input dari form
     * 2. Jika ada upload gambar, simpan ke storage/public/posts/
     * 3. Simpan data ke database pakai mass assignment
     * 4. Redirect ke halaman daftar post dengan notifikasi sukses
     */
    public function store(Request $request): RedirectResponse
    {
        // $request->validate([...]) — validasi input sesuai aturan
        // 'judul' wajib, string, max 255 karakter
        // 'deskripsi' wajib, string (teks panjang)
        // 'tanggal' wajib, format date (Y-m-d)
        // 'gambar' opsional (nullable), harus file gambar, max 2048 KB (2MB)
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ]);

        // hasFile('gambar') — cek apakah user upload file di field 'gambar'
        if ($request->hasFile('gambar')) {
            // store(path, disk) — simpan file ke storage/app/public/posts/
            // return String: path file (contoh: "posts/abc123.jpg")
            $validated['gambar'] = $request->file('gambar')->store('posts', 'public');
        }

        // Post::create($arrayData) — insert data ke tabel posts via mass assignment
        // Kolom yg terisi: judul, deskripsi, tanggal, gambar
        Post::create($validated);

        // redirect()->route(nama_route) — arahkan user ke halaman daftar post
        return redirect()->route('posts.index')
            ->with('success', 'Post berhasil dibuat.');
    }

    /**
     * show() — Menampilkan detail satu postingan
     * Method: GET /posts/{post}
     * Return: View dengan data satu post
     *
     * Route Model Binding: Parameter {post} otomatis diisi model Post
     * berdasarkan ID dari URL. Laravel otomatis query WHERE id = ... 
     */
    public function show(Post $post): View
    {
        // compact('post') — kirim data $post ke blade sebagai variabel $post
        return view('posts.show', compact('post'));
    }

    /**
     * edit() — Menampilkan form edit postingan
     * Method: GET /posts/{post}/edit
     * Return: View dengan data post yg akan diedit
     */
    public function edit(Post $post): View
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * update() — Memperbarui data postingan di database
     * Method: PUT/PATCH /posts/{post}
     * Return: RedirectResponse
     *
     * Alur:
     * 1. Validasi input sama seperti store()
     * 2. Jika upload gambar baru: hapus gambar lama, simpan yg baru
     * 3. Update data ke database
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        // Validasi sama seperti store — aturan fieldnya identik
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Cek apakah user upload file gambar baru
        if ($request->hasFile('gambar')) {
            // Jika post sebelumnya punya gambar, hapus dulu file lamanya
            if ($post->gambar) {
                // Storage::disk('public')->delete(path) — hapus file dari storage
                Storage::disk('public')->delete($post->gambar);
            }
            // Simpan file gambar baru, simpan path-nya ke array validated
            $validated['gambar'] = $request->file('gambar')->store('posts', 'public');
        }

        // $post->update($arrayData) — update data di database (hanya kolom yg ada di array)
        $post->update($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post berhasil diperbarui.');
    }

    /**
     * destroy() — Menghapus postingan dari database
     * Method: DELETE /posts/{post}
     * Return: RedirectResponse
     *
     * Alur:
     * 1. Hapus file gambar dari storage (jika ada)
     * 2. Hapus data dari database
     * 3. Redirect ke halaman daftar post dengan notifikasi
     */
    public function destroy(Post $post): RedirectResponse
    {
        // Hapus file gambar dari storage/public agar tidak menumpuk
        if ($post->gambar) {
            Storage::disk('public')->delete($post->gambar);
        }

        // $post->delete() — hapus baris data dari tabel posts
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post berhasil dihapus.');
    }
}
