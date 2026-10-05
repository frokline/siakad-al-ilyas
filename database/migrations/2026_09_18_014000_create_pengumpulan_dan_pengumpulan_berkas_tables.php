<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('kegiatan_id')
                ->constrained('kegiatan')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('detail_krs_id')
                ->constrained('detail_krs')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('pemilik_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->text('jawaban_teks')->nullable();

            $table->enum('status', [
                'terkirim',
                'dibatalkan',
            ])->default('terkirim');

            $table->dateTime('dikirim_at');
            $table->dateTime('diubah_at')->nullable();
            $table->dateTime('dibatalkan_at')->nullable();
            $table->dateTime('tenggat_snapshot');
            $table->unsignedInteger('revisi_kegiatan');
            $table->char('hash_jawaban', 64)->nullable();
            $table->unsignedInteger('revisi')->default(1);

            $table->timestamps();

            $table->unique(
                ['kegiatan_id', 'detail_krs_id'],
                'pengumpulan_peserta_unique'
            );

            $table->index(
                ['pemilik_id', 'status', 'kegiatan_id'],
                'pengumpulan_pemilik_status_index'
            );

            $table->index(
                ['kegiatan_id', 'status', 'dikirim_at'],
                'pengumpulan_kegiatan_status_index'
            );
        });

        Schema::create(
            'pengumpulan_berkas',
            function (Blueprint $table): void {
                $table->engine = 'InnoDB';

                $table->id();

                $table->foreignId('pengumpulan_id')
                    ->constrained('pengumpulan')
                    ->restrictOnDelete()
                    ->restrictOnUpdate();

                $table->foreignId('berkas_id')
                    ->constrained('berkas')
                    ->restrictOnDelete()
                    ->restrictOnUpdate();

                $table->string('nama_asli', 255);
                $table->string('mime_type', 150);
                $table->string('ekstensi', 10);
                $table->unsignedBigInteger('ukuran_byte');
                $table->char('sha256', 64);
                $table->boolean('aktif')->default(true);
                $table->dateTime('dilepas_at')->nullable();

                $table->timestamps();

                $table->unique(
                    ['pengumpulan_id', 'berkas_id'],
                    'pengumpulan_berkas_unique'
                );

                $table->index(
                    ['pengumpulan_id', 'aktif'],
                    'pengumpulan_berkas_aktif_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_berkas');
        Schema::dropIfExists('pengumpulan');
    }
};