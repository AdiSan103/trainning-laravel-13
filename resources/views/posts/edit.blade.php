@extends('layouts.app')
@section('title', 'Edit Post')

@section('content')
    <h1 class="text-2xl font-bold mb-4">✏️ Edit Post</h1>

    <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-medium mb-1">Judul</label>
            <input
                type="text"
                name="title"
                id="title"
                value="{{ old('title', $post->title) }}"
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
            >{{ old('body', $post->body) }}</textarea>
            @error('body')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        @if ($post->image_url)
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-1">Gambar Saat Ini</label>
                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-48 rounded mb-2">
            </div>
        @endif

        <div class="mb-4">
            <label for="image" class="block text-gray-700 font-medium mb-1">Ganti Gambar (opsional, max 2MB)</label>
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
            Perbarui
        </button>
        <a href="{{ route('posts.index') }}" class="text-gray-500 hover:text-gray-700 ml-2">Batal</a>
    </form>
@endsection
