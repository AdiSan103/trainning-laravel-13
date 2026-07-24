@extends('layouts.app')
@section('title', 'Buat Post Baru')

@section('content')
    <h1 class="text-2xl font-bold mb-4">✏️ Buat Post Baru</h1>

    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
        @csrf

        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-medium mb-1">Judul</label>
            <input
                type="text"
                name="title"
                id="title"
                value="{{ old('title') }}"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
            >
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="body" class="block text-gray-700 font-medium mb-1">Isi</label>
            <textarea
                name="body"
                id="body"
                rows="6"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
            >{{ old('body') }}</textarea>
            @error('body')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="block text-gray-700 font-medium mb-1">Gambar (opsional, max 2MB)</label>
            <input
                type="file"
                name="image"
                id="image"
                accept="image/*"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
            @error('image')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Simpan
        </button>
        <a href="{{ route('posts.index') }}" class="text-gray-500 hover:text-gray-700 ml-2">Batal</a>
    </form>
@endsection
