@extends('layouts.app')
@section('title', $post->title)

@section('content')
    <div class="bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold mb-2">{{ $post->title }}</h1>
        <p class="text-sm text-gray-400 mb-4">
            Dibuat {{ $post->created_at->format('d M Y, H:i') }}
            @if ($post->updated_at->ne($post->created_at))
                &middot; Diperbarui {{ $post->updated_at->diffForHumans() }}
            @endif
        </p>
        @if ($post->image_url)
            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full max-w-md rounded mb-4">
        @endif
        <hr class="mb-4">
        <div class="prose max-w-none text-gray-700 whitespace-pre-line">
            {{ $post->body }}
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <a href="{{ route('posts.edit', $post) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
            Edit
        </a>
        <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Hapus
            </button>
        </form>
        <a href="{{ route('posts.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
            Kembali
        </a>
    </div>
@endsection
