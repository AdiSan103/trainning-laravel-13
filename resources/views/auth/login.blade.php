{{-- @extends('layouts.app') — mewarisi layout dari layouts/app.blade.php --}}
@extends('layouts.app')
{{-- @section('title', 'Login') — mengisi section 'title' di layout jadi "Login" --}}
@section('title', 'Login')

{{-- @section('content') — memulai konten utama, akan dirender di @yield('content') layout --}}
@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-4">🔑 Login</h1>

        {{--
            route('login') — menghasilkan URL sesuai nama route 'login' (POST)
            method="POST" — form ini mengirim data POST
            @csrf — directive Blade untuk menambahkan token CSRF (wajib di semua form POST)
        --}}
        <form action="{{ route('login') }}" method="POST" class="bg-white rounded shadow p-6">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    {{-- old('email') — helper Laravel: tetap tampilkan input sebelumnya jika validasi gagal --}}
                    value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
                {{-- @error('email') — Blade directive: tampilkan pesan error untuk field 'email' --}}
                @error('email')
                    {{-- $message — variabel otomatis berisi pesan error validasi --}}
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    {{-- name="remember" — checkbox untuk fitur "remember me" (dicek via $request->filled('remember')) --}}
                    <input type="checkbox" name="remember" class="rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Login
            </button>

            <p class="mt-4 text-center text-sm text-gray-600">
                Belum punya akun?
                {{-- route('register') — link ke halaman route 'register' (GET) --}}
                <a href="{{ route('register') }}" class="text-blue-500 hover:underline">Daftar</a>
            </p>
        </form>
    </div>
{{-- @endsection — menutup section 'content' --}}
@endsection
