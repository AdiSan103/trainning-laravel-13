<?php

// ============================================================
// NAMESPACE: Menentukan lokasi folder class ini (App\Http\Controllers)
// ============================================================
namespace App\Http\Controllers;

// === IMPORT MODEL & CLASS BAWAAN LARAVEL ===
use App\Models\Login;                          // Model Login untuk query tabel login
use Illuminate\Http\RedirectResponse;          // Tipe return untuk redirect
use Illuminate\Http\Request;                   // Class untuk menangani data request HTTP
use Illuminate\Support\Facades\Auth;           // Facade Auth untuk otentikasi user
use Illuminate\View\View;                      // Tipe return untuk view/blade

// ============================================================
// CLASS AuthController — Mengatur semua fungsi otentikasi user
// ============================================================
class AuthController extends Controller
{
    /**
     * showRegister() — Menampilkan halaman form registrasi
     * Method: GET /register
     * Return: View (file blade resources/views/auth/register.blade.php)
     */
    public function showRegister(): View
    {
        // return view(nama_view) — memuat file blade .blade.php dari folder resources/views
        return view('auth.register');
    }

    /**
     * register() — Memproses data registrasi dari form
     * Method: POST /register
     * Return: RedirectResponse (redirect ke halaman lain)
     *
     * Alur:
     * 1. Validasi input dari form
     * 2. Simpan data ke database pakai model Login::create()
     * 3. Login otomatis user yang baru daftar
     * 4. Redirect ke halaman posts.index dengan pesan sukses
     */
    public function register(Request $request): RedirectResponse
    {
        // $request->validate([...]) — memvalidasi input sesuai aturan
        // Aturan: email wajib, string, format email, max 255, unique di tabel login
        // 'confirmed' otomatis cek field password_confirmation
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:login'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Login::create($arrayData) — Mass Assignment: insert data ke tabel login
        // Kolom diisi = email & password (password otomatis di-hash oleh model)
        $login = Login::create($validated);

        // Auth::login($user) — login user langsung setelah registrasi
        Auth::login($login);

        // redirect()->route(nama_route) — arahkan ke halaman sesuai nama route
        // ->with(key, value) — flash session untuk notifikasi sukses
        return redirect()->route('posts.index')
            ->with('success', 'Akun berhasil dibuat! Selamat datang.');
    }

    /**
     * showLogin() — Menampilkan halaman form login
     * Method: GET /login
     * Return: View
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * login() — Memproses login user
     * Method: POST /login
     * Return: RedirectResponse
     *
     * Alur:
     * 1. Validasi input email & password
     * 2. Auth::attempt() — coba cocokkan kredensial
     * 3. Jika cocok: regenerate session (cegah session fixation), redirect ke intended
     * 4. Jika gagal: kembali ke form login dengan pesan error
     */
    public function login(Request $request): RedirectResponse
    {
        // Validasi input: email & password wajib diisi
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Auth::attempt(kredensial, remember) — cek apakah data cocok di DB
        // $request->filled('remember') — cek apakah checkbox "remember me" dicentang
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // regenerate() — buat ulang session ID untuk keamanan (cegah session fixation)
            $request->session()->regenerate();

            // redirect()->intended(default) — arahkan ke halaman yg dituju sebelum login
            return redirect()->intended(route('posts.index'))
                ->with('success', 'Selamat datang kembali!');
        }

        // back() — kembali ke halaman sebelumnya (form login)
        // ->withErrors() — kirim error validasi ke session (ditangkap @error di blade)
        // ->onlyInput('email') — tetap tampilkan email yg diisi user sebelumnya
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * showProfile() — Menampilkan halaman profil user yang sedang login
     * Method: GET /profile
     * Return: View
     */
    public function showProfile(): View
    {
        return view('auth.profile');
    }

    /**
     * updateProfile() — Memperbarui data profil user (email)
     * Method: PUT/PATCH /profile
     * Return: RedirectResponse
     *
     * Aturan validasi unique:login,email,'.Auth::id().',id_login
     * Artinya: "unique di tabel login, kolom email, kecuali id milik user saat ini"
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        // Validasi: email unique kecuali milik user sendiri (biar bisa pakai email yg sama)
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:login,email,'.Auth::id().',id_login'],
        ]);

        // Auth::user() — ambil data user yg sedang login sebagai objek model
        // ->update() — update kolom di database
        Auth::user()->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * updatePassword() — Memperbarui password user
     * Method: PUT/PATCH /password
     * Return: RedirectResponse
     *
     * Aturan 'current_password' — rule bawaan Laravel untuk cek password saat ini
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // Validasi: current_password harus cocok, password baru min 8, harus di-confirm
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Update password — model otomatis hash password via mutator (Accessor/Mutator)
        Auth::user()->update(['password' => $validated['password']]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * logout() — Keluar dari session / logout user
     * Method: POST /logout
     * Return: RedirectResponse
     *
     * 3 langkah logout:
     * 1. Auth::logout() — hapus session otentikasi
     * 2. session()->invalidate() — hapus semua data session (cegah session fixation)
     * 3. session()->regenerateToken() — buat token CSRF baru
     */
    public function logout(Request $request): RedirectResponse
    {
        // Hapus data otentikasi user dari session
        Auth::logout();

        // Hapus/invalidasi seluruh session (keamanan: cegah session fixation)
        $request->session()->invalidate();
        // Buat ulang CSRF token biar token lama tidak bisa dipakai lagi
        $request->session()->regenerateToken();

        // Redirect ke halaman utama posts.index dengan notifikasi
        return redirect()->route('posts.index')
            ->with('success', 'Anda telah logout.');
    }
}
