<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('kelas_kuliah_id')
                ->constrained('kelas_kuliah')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->unsignedBigInteger('pertemuan_id')
                ->nullable();

            $table->foreignId('pembuat_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->uuid('form_token');

            $table->char('hash_permohonan', 64);

            $table->enum('jenis', [
                'materi',
                'tugas',
                'latihan',
                'uts',
                'uas',
            ]);

            $table->enum('metode', [
                'informasi',
                'pengumpulan_berkas',
            ])->default('informasi');

            $table->string('judul', 200);

            /*
             * Pesan atau penjelasan boleh kosong apabila dosen
             * hanya ingin membagikan berkas.
             */
            $table->text('instruksi')
                ->nullable();

            $table->text('tautan_eksternal')
                ->nullable();

            /*
             * Pembelajaran langsung diterbitkan.
             * buka_at dapat dipakai untuk menjadwalkan kapan
             * pembelajaran mulai dapat dibuka mahasiswa.
             */
            $table->dateTime('buka_at')
                ->nullable();

            /*
             * Materi tidak membutuhkan tenggat.
             * Tugas, latihan, UTS, dan UAS membutuhkan tenggat.
             */
            $table->dateTime('tenggat_at')
                ->nullable();

            /*
             * Ketentuan jawaban hanya dipakai untuk jenis
             * pembelajaran yang memerlukan pengumpulan.
             */
            $table->unsignedBigInteger('maks_ukuran_byte')
                ->nullable();

            $table->unsignedSmallInteger('maks_berkas')
                ->nullable();

            $table->json('ekstensi_diizinkan')
                ->nullable();

            /*
             * Tidak ada lagi status draf.
             * Data baru langsung terbit setelah validasi berhasil.
             */
            $table->enum('status', [
                'terbit',
                'ditutup',
                'arsip',
            ])->default('terbit');

            $table->dateTime('terbit_at');

            $table->dateTime('ditutup_at')
                ->nullable();

            $table->dateTime('diarsipkan_at')
                ->nullable();

            $table->unsignedInteger('revisi')
                ->default(1);

            $table->timestamps();

            $table->foreign(
                ['pertemuan_id', 'kelas_kuliah_id'],
                'kegiatan_pertemuan_kelas_fk'
            )
                ->references(['id', 'kelas_kuliah_id'])
                ->on('pertemuan')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->unique(
                ['pembuat_id', 'form_token'],
                'kegiatan_form_unique'
            );

            $table->unique(
                ['id', 'kelas_kuliah_id'],
                'kegiatan_identitas_kelas_unique'
            );

            $table->index(
                [
                    'kelas_kuliah_id',
                    'status',
                    'jenis',
                    'terbit_at',
                ],
                'kegiatan_kelas_status_index'
            );

            $table->index(
                ['status', 'tenggat_at'],
                'kegiatan_tenggat_index'
            );
        });

        Schema::create(
            'kegiatan_berkas',
            function (Blueprint $table): void {
                $table->engine = 'InnoDB';

                $table->id();

                $table->foreignId('kegiatan_id')
                    ->constrained('kegiatan')
                    ->restrictOnDelete()
                    ->restrictOnUpdate();

                $table->foreignId('berkas_id')
                    ->constrained('berkas')
                    ->restrictOnDelete()
                    ->restrictOnUpdate();

                $table->boolean('aktif')
                    ->default(true);

                $table->dateTime('dilepas_at')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    ['kegiatan_id', 'berkas_id'],
                    'kegiatan_berkas_unique'
                );

                $table->index(
                    ['kegiatan_id', 'aktif'],
                    'kegiatan_berkas_aktif_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_berkas');
        Schema::dropIfExists('kegiatan');
    }
};