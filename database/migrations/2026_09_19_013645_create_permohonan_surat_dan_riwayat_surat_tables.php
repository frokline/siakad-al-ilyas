<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_surat', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->string('nomor_pengajuan', 32)->unique();
            $t->foreignId('registrasi_semester_id')->constrained('registrasi_semester')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('mahasiswa_id')->constrained('mahasiswa')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('pemohon_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('jenis_surat_id')->constrained('jenis_surat')->restrictOnDelete()->restrictOnUpdate();
            $t->text('keperluan');
            $t->json('akademik_snapshot');
            $t->json('jenis_snapshot');
            $t->foreignId('lampiran_berkas_id')->nullable()->constrained('berkas')->restrictOnDelete()->restrictOnUpdate();
            $t->json('lampiran_snapshot')->nullable();
            $t->string('status', 20);
            $t->char('slot_aktif', 64)->nullable()->unique();
            $t->string('nomor_surat', 100)->nullable()->unique();
            $t->foreignId('hasil_berkas_id')->nullable()->constrained('berkas')->restrictOnDelete()->restrictOnUpdate();
            $t->json('hasil_snapshot')->nullable();
            $t->dateTime('diajukan_at');
            $t->dateTime('terbit_at')->nullable();
            $t->unsignedInteger('revisi')->default(1);
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->timestamps();
            $t->unique(['pemohon_id', 'form_token'], 'surat_form_unique');
            $t->unique('hasil_berkas_id', 'surat_hasil_unique');
            $t->index(['mahasiswa_id', 'id'], 'surat_mahasiswa_index');
            $t->index(['status', 'id'], 'surat_antrian_index');
        });
        Schema::create('riwayat_surat', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('permohonan_surat_id')->constrained('permohonan_surat')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('pelaku_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->string('status_lama', 20)->nullable();
            $t->string('status_baru', 20);
            $t->unsignedInteger('revisi_permohonan');
            $t->text('catatan');
            $t->dateTime('waktu');
            $t->timestamp('created_at')->nullable();
            $t->unique(['permohonan_surat_id', 'revisi_permohonan'], 'surat_riwayat_revisi_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('riwayat_surat');
        Schema::dropIfExists('permohonan_surat');
    }
};
