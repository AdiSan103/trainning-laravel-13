@extends('layouts.app')
@section('title', 'Edit Profil')

@section('content')
    <div class="max-w-md mx-auto">

        <h1 class="text-2xl font-bold mb-4">👤 Edit Profil</h1>

        {{-- route('profile.update') — arahkan ke controller updateProfile (PUT) --}}
        <form action="{{ route('profile.update') }}" method="POST" class="bg-white rounded shadow p-6 mb-6">
            @csrf
            {{-- @method('PUT') — Laravel method spoofing: form POST diubah jadi PUT (karena HTML cuma support GET/POST) --}}
            @method('PUT')

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    {{--
                        old('email', Auth::user()->email)
                        - old('email') — tampilkan input terakhir jika validasi gagal
                        - Auth::user()->email — fallback: ambil email dari user yang login saat ini
                    --}}
                    value="{{ old('email', Auth::user()->email) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Simpan Perubahan
            </button>
        </form>

        <h2 class="text-xl font-bold mb-4">🔒 Ganti Password</h2>

        {{-- route('profile.password') — arahkan ke controller updatePassword (PUT) --}}
        <form action="{{ route('profile.password') }}" method="POST" class="bg-white rounded shadow p-6">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="current_password" class="block text-gray-700 font-medium mb-1">Password Saat Ini</label>
                <input
                    type="password"
                    name="current_password"
                    id="current_password"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
                {{-- @error('current_password') — aturan validasi 'current_password' dari Laravel cek apakah cocok --}}
                @error('current_password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-1">Password Baru</label>
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
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Konfirmasi Password Baru</label>
                {{-- name="password_confirmation" — diperlukan aturan 'confirmed' di validasi --}}
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required
                >
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Ganti Password
            </button>
        </form>
    </div>
@endsection
