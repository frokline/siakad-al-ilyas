<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_semester', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('kurikulum_id')
                ->constrained('kurikulum')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->smallInteger('semester_studi');
            $table->smallInteger('versi');
            $table->string('nama', 100);

            $table->enum('status', [
                'draf',
                'diterbitkan',
                'arsip',
            ])->default('draf');

            $table->timestamps();

            $table->unique(
                ['kurikulum_id', 'semester_studi', 'versi'],
                'paket_identitas_unique'
            );

            $table->index(
                ['status', 'semester_studi'],
                'paket_status_semester_index'
            );
        });

        DB::statement(<<<'SQL'
            ALTER TABLE `paket_semester`
            ADD CONSTRAINT `paket_semester_positif_check`
                CHECK (`semester_studi` > 0),
            ADD CONSTRAINT `paket_versi_positif_check`
                CHECK (`versi` > 0)
        SQL);

        Schema::create('detail_paket', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('paket_semester_id')
                ->constrained('paket_semester')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('kurikulum_mata_kuliah_id')
                ->constrained('kurikulum_mata_kuliah')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['paket_semester_id', 'kurikulum_mata_kuliah_id'],
                'detail_paket_mata_kuliah_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_paket');
        Schema::dropIfExists('paket_semester');
    }
};
