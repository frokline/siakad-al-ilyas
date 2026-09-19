<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('program_studi_id')
                ->constrained('program_studi')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('kode', 40);
            $table->string('nama', 150);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(
                ['program_studi_id', 'kode'],
                'mata_kuliah_prodi_kode_unique'
            );

            $table->index(['aktif', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
