<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kurikulum_mata_kuliah', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('kurikulum_id')
                ->constrained('kurikulum')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('mata_kuliah_id')
                ->constrained('mata_kuliah')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->decimal('sks', 4, 1);
            $table->smallInteger('semester_rekomendasi');
            $table->enum('sifat', ['wajib', 'pilihan'])->default('wajib');

            $table->timestamps();

            $table->unique(
                ['kurikulum_id', 'mata_kuliah_id'],
                'kmk_kurikulum_mk_unique'
            );

            $table->index(
                ['kurikulum_id', 'semester_rekomendasi'],
                'kmk_kurikulum_semester_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kurikulum_mata_kuliah');
    }
};
