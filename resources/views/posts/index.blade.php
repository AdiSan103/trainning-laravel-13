@extends('layouts.app')
@section('title', 'Semua Post')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">📋 Semua Post</h1>
        <a href="{{ route('posts.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            + Buat Post Baru
        </a>
    </div>

    @if ($posts->isEmpty())
        <p class="text-gray-500 text-center py-8">Belum ada post. <a href="{{ route('posts.create') }}" class="text-blue-500 underline">Buat yang pertama!</a></p>
    @else
        <div class="space-y-4">
            @foreach ($posts as $post)
                <div class="bg-white rounded shadow p-4">
                    <h2 class="text-lg font-semibold">
                        <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline">
                            {{ $post->title }}
                        </a>
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">{{ Str::limit($post->body, 100) }}</p>
                    <div class="mt-2 text-sm text-gray-400">
                        Dibuat {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
