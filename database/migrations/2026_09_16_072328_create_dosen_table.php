<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('kode_dosen', 40)->unique();
            $table->string('nidn', 40)->nullable()->unique();
            $table->string('gelar', 100)->nullable();

            $table->enum('status', ['aktif', 'nonaktif'])
                ->default('aktif');

            $table->timestamps();

            $table->index(['status', 'kode_dosen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
