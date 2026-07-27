<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(),
            'tanggal' => fake()->date(),
            'deskripsi' => fake()->paragraphs(3, true),
            'gambar' => null,
        ];
    }
}
