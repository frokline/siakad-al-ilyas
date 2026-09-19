<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rombel', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('periode_akademik_id')
                ->constrained('periode_akademik')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('paket_semester_id')
                ->constrained('paket_semester')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('kode', 40);
            $table->smallInteger('kapasitas')->nullable();

            $table->timestamps();

            $table->unique(
                ['periode_akademik_id', 'kode'],
                'rombel_periode_kode_unique'
            );

            // Digunakan oleh FK gabungan pada registrasi semester.
            $table->unique(
                ['id', 'periode_akademik_id'],
                'rombel_id_periode_unique'
            );
        });

        DB::statement(<<<'SQL'
            ALTER TABLE `rombel`
            ADD CONSTRAINT `rombel_kapasitas_check`
                CHECK (`kapasitas` IS NULL OR `kapasitas` > 0)
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('rombel');
    }
};
