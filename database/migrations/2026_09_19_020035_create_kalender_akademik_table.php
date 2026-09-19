<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kalender_akademik', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('periode_akademik_id')->constrained('periode_akademik')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('program_studi_id')->nullable()->constrained('program_studi')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('pembuat_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->string('judul', 200);
            $t->text('keterangan')->nullable();
            $t->string('jenis', 30);
            $t->dateTime('mulai_at');
            $t->dateTime('selesai_at');
            $t->string('status', 20)->default('draf');
            $t->dateTime('diterbitkan_at')->nullable();
            $t->dateTime('dibatalkan_at')->nullable();
            $t->text('catatan_perubahan')->nullable();
            $t->unsignedInteger('revisi')->default(1);
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->timestamps();
            $t->unique(['pembuat_id', 'form_token'], 'kalender_form_unique');
            $t->index(['status', 'mulai_at'], 'kalender_status_mulai_index');
            $t->index(['periode_akademik_id', 'mulai_at'], 'kalender_periode_mulai_index');
            $t->index(['program_studi_id', 'mulai_at'], 'kalender_prodi_mulai_index');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('kalender_akademik');
    }
};
