<!DOCTYPE html>
{{--
    str_replace('_', '-', app()->getLocale())
    — mengambil locale aplikasi (misal: id_ID → id-ID) untuk atribut lang HTML
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{--
        config('app.name', 'Laravel') — ambil nama aplikasi dari config/app.php, fallback "Laravel"
        @yield('title', 'Posts') — isi dari section 'title' child view, default 'Posts'
    --}}
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Posts')</title>
    {{-- @if (file_exists(...)) — cek apakah Vite sudah build (manifest.json ada) atau dev server aktif (hot) --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        {{-- @vite — directive Laravel untuk muat file CSS/JS via Vite (build otomatis) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow mb-6">
        <div class="max-w-4xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <a href="/" class="text-xl font-bold text-gray-800">📝 Belajar Laravel</a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('posts.index') }}" class="text-gray-600 hover:text-gray-800">Posts</a>
                    {{-- @auth — directive Blade: hanya tampil jika user sudah login --}}
                    @auth
                        {{-- Auth::user()->email — menampilkan email user yang login --}}
                        <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-800">👤 {{ Auth::user()->email }}</a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Logout</button>
                        </form>
                    {{-- @else — tampil jika user BELUM login --}}
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-800">Login</a>
                        <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-800">Daftar</a>
                    {{-- @endauth — penutup @auth/@else --}}
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4">
        {{-- session('success') — flash message dari controller (redirect()->with('success', ...)) --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- @yield('content') — tempat di mana child view (halaman lain) menaruh kontennya --}}
        @yield('content')
    </main>
</body>
</html>
