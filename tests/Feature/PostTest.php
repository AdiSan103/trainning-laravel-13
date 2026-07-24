<?php

use App\Models\Post;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('post baru dapat disimpan dengan gambar', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('foto.jpg', 800, 600);

    $response = $this->post(route('posts.store'), [
        'title' => 'Post Dengan Gambar',
        'body' => 'Isi post dengan gambar.',
        'image' => $image,
    ]);

    $response->assertRedirect(route('posts.index'));

    $post = Post::where('title', 'Post Dengan Gambar')->first();
    $this->assertNotNull($post->image);
    $this->assertStringStartsWith('posts/', $post->image);
});

test('post dapat diperbarui dengan gambar baru', function () {
    Storage::fake('public');
    $oldImage = UploadedFile::fake()->image('lama.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['image' => $oldImage]);

    $newImage = UploadedFile::fake()->image('baru.jpg', 800, 600);

    $response = $this->put(route('posts.update', $post), [
        'title' => 'Judul Baru',
        'body' => 'Isi baru dengan gambar baru.',
        'image' => $newImage,
    ]);

    $response->assertRedirect(route('posts.index'));

    $post->refresh();
    $this->assertNotEquals($oldImage, $post->image);
    Storage::disk('public')->assertExists($post->image);
    Storage::disk('public')->assertMissing($oldImage);
});

test('post dapat diperbarui tanpa mengubah gambar', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('tetap.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['image' => $image]);

    $response = $this->put(route('posts.update', $post), [
        'title' => 'Judul Diubah',
        'body' => 'Isi diubah, gambar tetap.',
    ]);

    $response->assertRedirect(route('posts.index'));

    $post->refresh();
    $this->assertEquals($image, $post->image);
    Storage::disk('public')->assertExists($image);
});

test('gambar dihapus bersama post', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('hapus.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['image' => $image]);

    $this->delete(route('posts.destroy', $post));

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    Storage::disk('public')->assertMissing($image);
});
