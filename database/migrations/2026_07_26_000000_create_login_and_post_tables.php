<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old tables
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');

        // Create new login table
        Schema::create('login', function (Blueprint $table) {
            $table->id('id_login');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // Create new post table
        Schema::create('post', function (Blueprint $table) {
            $table->id('id_post');
            $table->string('judul');
            $table->date('tanggal');
            $table->string('gambar')->nullable();
            $table->text('deskripsi');
            $table->timestamps();
        });

        // Recreate sessions table (used by Laravel)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post');
        Schema::dropIfExists('login');
        Schema::dropIfExists('sessions');
    }
};
