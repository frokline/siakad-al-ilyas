<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrasi_semester', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('riwayat_studi_id')
                ->constrained('riwayat_studi')->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('rombel_id');
            $table->foreignId('periode_akademik_id')
                ->constrained('periode_akademik')->restrictOnDelete()->restrictOnUpdate();
            $table->smallInteger('semester_studi');
            $table->enum('status', ['terdaftar', 'aktif', 'cuti', 'batal'])
                ->default('terdaftar');
            $table->text('alasan_status')->nullable();
            $table->timestamp('penempatan_dikunci_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();

            $table->unique(
                ['riwayat_studi_id', 'periode_akademik_id'],
                'registrasi_riwayat_periode_unique'
            );
            $table->index(['rombel_id', 'status'], 'registrasi_rombel_status_index');
            $table->index(['periode_akademik_id', 'status'], 'registrasi_periode_status_index');
            $table->foreign(
                ['rombel_id', 'periode_akademik_id'],
                'registrasi_rombel_periode_foreign'
            )->references(['id', 'periode_akademik_id'])
                ->on('rombel')->restrictOnDelete()->restrictOnUpdate();
        });

        // Target database proyek: MariaDB / MySQL.
        DB::statement(
            'ALTER TABLE registrasi_semester
             ADD CONSTRAINT registrasi_semester_positif CHECK (semester_studi > 0),
             ADD CONSTRAINT registrasi_revisi_positif CHECK (revisi > 0),
             ADD CONSTRAINT registrasi_aktif_terkunci
                 CHECK (status <> \'aktif\' OR penempatan_dikunci_at IS NOT NULL),
             ADD CONSTRAINT registrasi_alasan_wajib
                 CHECK (status NOT IN (\'cuti\', \'batal\')
                     OR (alasan_status IS NOT NULL
                         AND CHAR_LENGTH(TRIM(alasan_status)) >= 10))'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('registrasi_semester');
    }
};
