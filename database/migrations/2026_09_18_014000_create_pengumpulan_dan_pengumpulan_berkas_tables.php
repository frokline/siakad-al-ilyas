<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('kegiatan_id')->constrained('kegiatan')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('detail_krs_id')->constrained('detail_krs')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('pemilik_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->unsignedInteger('versi');
            $t->uuid('token_draf');
            $t->char('kunci_kirim', 64)->unique('pengumpulan_kunci_kirim_unique');
            $t->text('jawaban_teks')->nullable();
            $t->enum('status', ['draf', 'dikirim'])->default('draf');
            // 1 ketika draf, NULL ketika dikirim. UNIQUE membolehkan banyak NULL.
            $t->unsignedTinyInteger('penanda_draf')->nullable()->default(1);
            $t->dateTime('dikirim_at')->nullable();
            $t->dateTime('tenggat_snapshot')->nullable();
            $t->unsignedInteger('revisi_kegiatan')->nullable();
            $t->char('hash_jawaban', 64)->nullable();
            $t->unsignedInteger('revisi')->default(1);
            $t->timestamps();
            $t->unique(['kegiatan_id', 'detail_krs_id', 'versi'], 'pengumpulan_peserta_versi_unique');
            $t->unique(['kegiatan_id', 'detail_krs_id', 'penanda_draf'], 'pengumpulan_satu_draf_unique');
            $t->unique(['pemilik_id', 'token_draf'], 'pengumpulan_token_draf_unique');
            $t->index(['kegiatan_id', 'detail_krs_id', 'status', 'versi'], 'pengumpulan_efektif_index');
            $t->index(['pemilik_id', 'kegiatan_id', 'id'], 'pengumpulan_pemilik_index');
        });
        Schema::create('pengumpulan_berkas', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('pengumpulan_id')->constrained('pengumpulan')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('berkas_id')->constrained('berkas')->restrictOnDelete()->restrictOnUpdate();
            $t->string('nama_asli', 255);
            $t->string('mime_type', 100);
            $t->string('ekstensi', 10);
            $t->unsignedBigInteger('ukuran_byte');
            $t->char('sha256', 64);
            $t->boolean('aktif')->default(true);
            $t->dateTime('dilepas_at')->nullable();
            $t->timestamps();
            $t->unique(['pengumpulan_id', 'berkas_id'], 'pengumpulan_berkas_unique');
            $t->index(['pengumpulan_id', 'aktif'], 'pengumpulan_berkas_aktif_index');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_berkas');
        Schema::dropIfExists('pengumpulan');
    }
};
