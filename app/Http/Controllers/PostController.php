<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::latest()->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post berhasil dibuat.');
    }

    public function show(Post $post): View
    {
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post): View
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post berhasil dihapus.');
    }
}
