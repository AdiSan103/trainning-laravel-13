{{-- @extends('layouts.app') — mewarisi layout dari layouts/app.blade.php --}}
@extends('layouts.app')
{{-- @section('title', 'Daftar') — ganti judul halaman jadi "Daftar" --}}
@section('title', 'Daftar')

{{-- @section('content') — awal konten utama halaman --}}
@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-4">📝 Daftar Akun</h1>

        {{--
            route('register') — URL sesuai nama route 'register' (POST)
            method="POST" — form POST
            @csrf — token CSRF wajib
        --}}
        <form action="{{ route('register') }}" method="POST" class="bg-white rounded shadow p-6">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    {{-- old('email') — pertahankan input user jika validasi gagal --}}
                    value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
                {{-- @error('email') — tampilkan error validasi field email --}}
                @error('email')
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
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Konfirmasi Password</label>
                {{--
                    name="password_confirmation" — aturan validasi 'confirmed' di controller otomatis
                    mencari field dengan nama ini untuk mencocokkan password
                --}}
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Daftar
            </button>

            <p class="mt-4 text-center text-sm text-gray-600">
                Sudah punya akun?
                {{-- route('login') — link ke halama login --}}
                <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Login</a>
            </p>
        </form>
    </div>
@endsection
