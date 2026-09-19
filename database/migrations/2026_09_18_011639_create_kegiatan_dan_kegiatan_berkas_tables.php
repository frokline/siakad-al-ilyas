<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $t->unsignedBigInteger('pertemuan_id')->nullable();
            $t->foreignId('pembuat_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->enum('jenis', ['tugas', 'latihan', 'uts', 'uas']);
            $t->enum('metode', ['pengumpulan_berkas'])->default('pengumpulan_berkas');
            $t->string('judul', 200);
            $t->text('instruksi');
            $t->dateTime('buka_at');
            $t->dateTime('tenggat_at');
            $t->unsignedBigInteger('maks_ukuran_byte');
            $t->unsignedSmallInteger('maks_berkas');
            $t->json('ekstensi_diizinkan');
            $t->enum('status', ['draf', 'terbit', 'ditutup', 'arsip'])->default('draf');
            $t->dateTime('terbit_at')->nullable();
            $t->dateTime('ditutup_at')->nullable();
            $t->dateTime('diarsipkan_at')->nullable();
            $t->unsignedInteger('revisi')->default(1);
            $t->timestamps();
            $t->foreign(['pertemuan_id', 'kelas_kuliah_id'], 'kegiatan_pertemuan_kelas_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('pertemuan')->restrictOnDelete()->restrictOnUpdate();
            $t->unique(['pembuat_id', 'form_token'], 'kegiatan_form_unique');
            $t->unique(['id', 'kelas_kuliah_id'], 'kegiatan_identitas_kelas_unique');
            $t->index(['kelas_kuliah_id', 'status', 'buka_at'], 'kegiatan_kelas_status_index');
            $t->index(['status', 'tenggat_at'], 'kegiatan_tenggat_index');
        });
        Schema::create('kegiatan_berkas', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('kegiatan_id')->constrained('kegiatan')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('berkas_id')->constrained('berkas')->restrictOnDelete()->restrictOnUpdate();
            $t->boolean('aktif')->default(true);
            $t->dateTime('dilepas_at')->nullable();
            $t->timestamps();
            $t->unique(['kegiatan_id', 'berkas_id'], 'kegiatan_berkas_unique');
            $t->index(['kegiatan_id', 'aktif'], 'kegiatan_berkas_aktif_index');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_berkas');
        Schema::dropIfExists('kegiatan');
    }
};
