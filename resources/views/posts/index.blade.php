{{-- @extends('layouts.app') — mewarisi layout utama --}}
@extends('layouts.app')
{{-- @section('title', 'Semua Post') — ganti judul halaman --}}
@section('title', 'Semua Post')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">📋 Semua Post</h1>
        {{-- route('posts.create') — link ke halaman buat post baru --}}
        <a href="{{ route('posts.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            + Buat Post Baru
        </a>
    </div>

    {{-- $posts->isEmpty() — cek apakah data posts kosong (dari pagination) --}}
    @if ($posts->isEmpty())
        <p class="text-gray-500 text-center py-8">Belum ada post. <a href="{{ route('posts.create') }}" class="text-blue-500 underline">Buat yang pertama!</a></p>
    @else
        <div class="space-y-4">
            {{-- @foreach ($posts as $post) — loop data posts dari controller --}}
            @foreach ($posts as $post)
                <div class="bg-white rounded shadow p-4">
                    <div class="flex gap-4">
                        {{-- $post->gambar_url — accessor dari Post model (URL absolut gambar) --}}
                        @if ($post->gambar_url)
                            <img src="{{ $post->gambar_url }}" alt="{{ $post->judul }}" class="w-24 h-24 object-cover rounded flex-shrink-0">
                        @endif
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{-- route('posts.show', $post) — link ke detail post (Route Model Binding via ID) --}}
                                <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline">
                                    {{ $post->judul }}
                                </a>
                            </h2>
                            {{-- Str::limit($post->deskripsi, 100) — helper Laravel: potong teks max 100 karakter --}}
                            <p class="text-gray-600 text-sm mt-1">{{ Str::limit($post->deskripsi, 100) }}</p>
                            <div class="mt-2 text-sm text-gray-400">
                                {{-- $post->tanggal->format('d M Y') — tanggal sudah di-cast ke Carbon, bisa panggil method format() --}}
                                {{ $post->tanggal ? $post->tanggal->format('d M Y') : '' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{-- $posts->links() — menampilkan tombol pagination (Previous/Next atau nomor halaman) --}}
            {{ $posts->links() }}
        </div>
    @endif
@endsection
