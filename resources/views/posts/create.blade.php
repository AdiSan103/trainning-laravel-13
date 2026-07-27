{{-- @extends('layouts.app') — mewarisi layout utama --}}
@extends('layouts.app')
{{-- @section('title', 'Buat Post Baru') — ganti judul halaman --}}
@section('title', 'Buat Post Baru')

@section('content')
    <h1 class="text-2xl font-bold mb-4">✏️ Buat Post Baru</h1>

    {{--
        route('posts.store') — URL untuk menyimpan post (POST)
        enctype="multipart/form-data" — WAJIB agar form bisa upload file (gambar)
    --}}
    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
        @csrf

        <div class="mb-4">
            <label for="judul" class="block text-gray-700 font-medium mb-1">Judul</label>
            <input
                type="text"
                name="judul"
                id="judul"
                {{-- old('judul') — pertahankan input jika validasi gagal --}}
                value="{{ old('judul') }}"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
            >
            @error('judul')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="deskripsi" class="block text-gray-700 font-medium mb-1">Deskripsi</label>
            <textarea
                name="deskripsi"
                id="deskripsi"
                rows="6"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
            >{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="tanggal" class="block text-gray-700 font-medium mb-1">Tanggal</label>
            <input
                type="date"
                name="tanggal"
                id="tanggal"
                {{-- old('tanggal') — tetap tampilkan tanggal yg diisi sebelumnya jika validasi gagal --}}
                value="{{ old('tanggal') }}"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
            >
            @error('tanggal')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="gambar" class="block text-gray-700 font-medium mb-1">Gambar (opsional, max 2MB)</label>
            <input
                type="file"
                name="gambar"
                id="gambar"
                accept="image/*"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
            @error('gambar')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Simpan
        </button>
        <a href="{{ route('posts.index') }}" class="text-gray-500 hover:text-gray-700 ml-2">Batal</a>
    </form>
@endsection
