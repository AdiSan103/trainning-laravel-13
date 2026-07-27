<?php

use App\Models\Post;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('halaman index menampilkan semua post', function () {
    $posts = Post::factory()->count(3)->create();

    $response = $this->get(route('posts.index'));

    $response->assertStatus(200);
    $response->assertSee($posts[0]->judul);
});

test('halaman create dapat diakses', function () {
    $response = $this->get(route('posts.create'));

    $response->assertStatus(200);
});

test('post baru dapat disimpan', function () {
    $response = $this->post(route('posts.store'), [
        'judul' => 'Judul Post Baru',
        'deskripsi' => 'Ini adalah deskripsi post baru.',
        'tanggal' => '2026-07-26',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('post', ['judul' => 'Judul Post Baru']);
});

test('halaman show menampilkan detail post', function () {
    $post = Post::factory()->create();

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200);
    $response->assertSee($post->judul);
    $response->assertSee($post->deskripsi);
});

test('halaman edit dapat diakses', function () {
    $post = Post::factory()->create();

    $response = $this->get(route('posts.edit', $post));

    $response->assertStatus(200);
    $response->assertSee($post->judul);
});

test('post dapat diperbarui', function () {
    $post = Post::factory()->create();

    $response = $this->put(route('posts.update', $post), [
        'judul' => 'Judul Diperbarui',
        'deskripsi' => 'Deskripsi yang sudah diperbarui.',
        'tanggal' => '2026-07-27',
    ]);

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('post', [
        'id_post' => $post->id_post,
        'judul' => 'Judul Diperbarui',
    ]);
});

test('post dapat dihapus', function () {
    $post = Post::factory()->create();

    $response = $this->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseMissing('post', ['id_post' => $post->id_post]);
});

test('validasi judul wajib diisi saat create', function () {
    $response = $this->post(route('posts.store'), [
        'judul' => '',
        'deskripsi' => 'Deskripsi saja tanpa judul.',
        'tanggal' => '2026-07-26',
    ]);

    $response->assertSessionHasErrors('judul');
});

test('validasi deskripsi wajib diisi saat create', function () {
    $response = $this->post(route('posts.store'), [
        'judul' => 'Judul',
        'deskripsi' => '',
        'tanggal' => '2026-07-26',
    ]);

    $response->assertSessionHasErrors('deskripsi');
});

test('post baru dapat disimpan dengan gambar', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('foto.jpg', 800, 600);

    $response = $this->post(route('posts.store'), [
        'judul' => 'Post Dengan Gambar',
        'deskripsi' => 'Deskripsi post dengan gambar.',
        'tanggal' => '2026-07-26',
        'gambar' => $image,
    ]);

    $response->assertRedirect(route('posts.index'));

    $post = Post::where('judul', 'Post Dengan Gambar')->first();
    $this->assertNotNull($post->gambar);
    $this->assertStringStartsWith('posts/', $post->gambar);
});

test('post dapat diperbarui dengan gambar baru', function () {
    Storage::fake('public');
    $oldImage = UploadedFile::fake()->image('lama.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['gambar' => $oldImage]);

    $newImage = UploadedFile::fake()->image('baru.jpg', 800, 600);

    $response = $this->put(route('posts.update', $post), [
        'judul' => 'Judul Baru',
        'deskripsi' => 'Deskripsi baru dengan gambar baru.',
        'tanggal' => '2026-07-27',
        'gambar' => $newImage,
    ]);

    $response->assertRedirect(route('posts.index'));

    $post->refresh();
    $this->assertNotEquals($oldImage, $post->gambar);
    Storage::disk('public')->assertExists($post->gambar);
    Storage::disk('public')->assertMissing($oldImage);
});

test('post dapat diperbarui tanpa mengubah gambar', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('tetap.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['gambar' => $image]);

    $response = $this->put(route('posts.update', $post), [
        'judul' => 'Judul Diubah',
        'deskripsi' => 'Deskripsi diubah, gambar tetap.',
        'tanggal' => '2026-07-27',
    ]);

    $response->assertRedirect(route('posts.index'));

    $post->refresh();
    $this->assertEquals($image, $post->gambar);
    Storage::disk('public')->assertExists($image);
});

test('gambar dihapus bersama post', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('hapus.jpg')->store('posts', 'public');
    $post = Post::factory()->create(['gambar' => $image]);

    $this->delete(route('posts.destroy', $post));

    $this->assertDatabaseMissing('post', ['id_post' => $post->id_post]);
    Storage::disk('public')->assertMissing($image);
});
