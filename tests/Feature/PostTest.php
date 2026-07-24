<?php

use App\Models\Post;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('halaman index menampilkan semua post', function () {
    $posts = Post::factory()->count(3)->create();

    $response = $this->get(route('posts.index'));

    $response->assertStatus(200);
    $response->assertSee($posts[0]->title);
});

test('halaman create dapat diakses', function () {
    $response = $this->get(route('posts.create'));

    $response->assertStatus(200);
});

test('post baru dapat disimpan', function () {
    $response = $this->post(route('posts.store'), [
        'title' => 'Judul Post Baru',
        'body' => 'Ini adalah isi dari post baru.',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('posts', ['title' => 'Judul Post Baru']);
});

test('halaman show menampilkan detail post', function () {
    $post = Post::factory()->create();

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200);
    $response->assertSee($post->title);
    $response->assertSee($post->body);
});

test('halaman edit dapat diakses', function () {
    $post = Post::factory()->create();

    $response = $this->get(route('posts.edit', $post));

    $response->assertStatus(200);
    $response->assertSee($post->title);
});

test('post dapat diperbarui', function () {
    $post = Post::factory()->create();

    $response = $this->put(route('posts.update', $post), [
        'title' => 'Judul Diperbarui',
        'body' => 'Isi yang sudah diperbarui.',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Judul Diperbarui',
    ]);
});

test('post dapat dihapus', function () {
    $post = Post::factory()->create();

    $response = $this->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('validasi title wajib diisi saat create', function () {
    $response = $this->post(route('posts.store'), [
        'title' => '',
        'body' => 'Isi saja tanpa judul.',
    ]);

    $response->assertSessionHasErrors('title');
});

test('validasi body wajib diisi saat create', function () {
    $response = $this->post(route('posts.store'), [
        'title' => 'Judul',
        'body' => '',
    ]);

    $response->assertSessionHasErrors('body');
});
