<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username', 100)->unique();
            $table->string('email', 190)->unique();
            $table->string('password_hash', 255);

            $table->string('nama', 150);
            $table->string('telepon', 30)->nullable();

            $table->enum('status', [
                'aktif',
                'nonaktif',
            ])->default('aktif');

            $table->rememberToken();
            $table->timestamps();

            $table->index(['status', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};