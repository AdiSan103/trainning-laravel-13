{{-- @extends('layouts.app') — mewarisi layout utama --}}
@extends('layouts.app')
{{-- @section('title', $post->judul) — judul halaman = judul post dari database --}}
@section('title', $post->judul)

@section('content')
    <div class="bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold mb-2">{{ $post->judul }}</h1>
        <p class="text-sm text-gray-400 mb-4">
            {{-- $post->tanggal->format('d M Y') — format tanggal dari Carbon --}}
            {{ $post->tanggal ? $post->tanggal->format('d M Y') : '' }}
        </p>
        {{-- $post->gambar_url — accessor untuk URL absolut gambar --}}
        @if ($post->gambar_url)
            <img src="{{ $post->gambar_url }}" alt="{{ $post->judul }}" class="w-full max-w-md rounded mb-4">
        @endif
        <hr class="mb-4">
        <div class="prose max-w-none text-gray-700 whitespace-pre-line">
            {{-- $post->deskripsi — teks deskripsi, whitespace-pre-line agar enter/newline tampil --}}
            {{ $post->deskripsi }}
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        {{-- route('posts.edit', $post) — link ke halaman edit --}}
        <a href="{{ route('posts.edit', $post) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
            Edit
        </a>
        {{--
            route('posts.destroy', $post) — hapus post (DELETE)
            onsubmit="return confirm(...)" — konfirmasi JavaScript sebelum submit form
        --}}
        <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
            @csrf
            {{-- @method('DELETE') — spoofing method jadi DELETE --}}
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
