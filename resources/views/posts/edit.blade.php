{{-- @extends('layouts.app') — mewarisi layout utama --}}
@extends('layouts.app')
@section('title', 'Edit Post')

@section('content')
    <h1 class="text-2xl font-bold mb-4">✏️ Edit Post</h1>

    {{--
        route('posts.update', $post) — URL untuk update (PUT)
        $post otomatis dikirim sebagai ID via Route Model Binding
        enctype="multipart/form-data" — wajib untuk upload file
    --}}
    <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow p-6">
        @csrf
        {{-- @method('PUT') — spoofing method jadi PUT (karena HTML hanya GET/POST) --}}
        @method('PUT')

        <div class="mb-4">
            <label for="judul" class="block text-gray-700 font-medium mb-1">Judul</label>
            <input
                type="text"
                name="judul"
                id="judul"
                {{-- old('judul', $post->judul) — old() dulu, fallback ke data post saat ini --}}
                value="{{ old('judul', $post->judul) }}"
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
            >{{ old('deskripsi', $post->deskripsi) }}</textarea>
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
                {{--
                    $post->tanggal?->format('Y-m-d')
                    — tanggal di-cast ke Carbon (dari model), format ke Y-m-d untuk input type date
                    ?-> (nullsafe) — aman jika null
                --}}
                value="{{ old('tanggal', $post->tanggal?->format('Y-m-d')) }}"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
            >
            @error('tanggal')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- $post->gambar_url — accessor dari Post model: bikin URL absolut dari path gambar --}}
        @if ($post->gambar_url)
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-1">Gambar Saat Ini</label>
                {{-- Tampilkan preview gambar yang sudah ada --}}
                <img src="{{ $post->gambar_url }}" alt="{{ $post->judul }}" class="w-48 rounded mb-2">
            </div>
        @endif

        <div class="mb-4">
            <label for="gambar" class="block text-gray-700 font-medium mb-1">Ganti Gambar (opsional, max 2MB)</label>
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
            Perbarui
        </button>
        <a href="{{ route('posts.index') }}" class="text-gray-500 hover:text-gray-700 ml-2">Batal</a>
    </form>
@endsection
