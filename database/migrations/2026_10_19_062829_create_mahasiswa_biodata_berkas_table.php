<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_biodata_berkas', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('mahasiswa_id')
                ->constrained('mahasiswa')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('berkas_id')
                ->constrained('berkas')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->enum('jenis_berkas', [
                'akta_kelahiran',
                'ijazah_terakhir',
                'surat_rekomendasi_pindah',
                'transkrip',
                'ktp',
                'kartu_keluarga',
                'pas_foto_3x4',
            ]);

            $table->boolean('aktif')->default(true);
            $table->timestamp('dilepas_at')->nullable();

            $table->timestamps();

            // Satu berkas tidak boleh memiliki relasi ganda
            // untuk mahasiswa dan jenis dokumen yang sama.
            $table->unique(
                ['mahasiswa_id', 'berkas_id', 'jenis_berkas'],
                'mhs_biodata_berkas_unique'
            );

            $table->index(
                ['mahasiswa_id', 'jenis_berkas', 'aktif'],
                'mhs_biodata_berkas_aktif_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_biodata_berkas');
    }
};
