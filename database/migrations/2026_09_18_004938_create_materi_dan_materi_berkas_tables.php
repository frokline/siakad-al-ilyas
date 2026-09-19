<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('pertemuan_id')->nullable();
            $table->foreignId('pembuat_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->uuid('form_token');
            $table->char('hash_permohonan', 64);
            $table->string('judul', 200);
            $table->text('isi')->nullable();
            $table->text('tautan_eksternal')->nullable();
            $table->enum('status', ['draf', 'terbit', 'arsip'])->default('draf');
            $table->dateTime('terbit_at')->nullable();
            $table->dateTime('diarsipkan_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
            $table->foreign(['pertemuan_id', 'kelas_kuliah_id'], 'materi_pertemuan_kelas_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('pertemuan')->restrictOnDelete()->restrictOnUpdate();
            $table->unique(['pembuat_id', 'form_token'], 'materi_form_unique');
            $table->index(['kelas_kuliah_id', 'status', 'terbit_at'], 'materi_kelas_status_index');
        });
        Schema::create('materi_berkas', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('materi_id')->constrained('materi')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('berkas_id')->constrained('berkas')->restrictOnDelete()->restrictOnUpdate();
            $table->boolean('aktif')->default(true);
            $table->dateTime('dilepas_at')->nullable();
            $table->timestamps();
            $table->unique(['materi_id', 'berkas_id'], 'materi_berkas_unique');
            $table->index(['materi_id', 'aktif'], 'materi_berkas_aktif_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_berkas');
        Schema::dropIfExists('materi');
    }
};
