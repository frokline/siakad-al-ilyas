<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_surat', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->string('kode', 30)->unique('jenis_surat_kode_unique');
            $t->string('nama', 100);
            $t->text('syarat')->nullable();
            $t->boolean('aktif')->default(true);
            $t->dateTime('dinonaktifkan_at')->nullable();
            $t->foreignId('pembuat_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->unsignedInteger('revisi')->default(1);
            $t->timestamps();
            $t->unique(['pembuat_id', 'form_token'], 'jenis_surat_form_unique');
            $t->index(['aktif', 'kode'], 'jenis_surat_status_kode_index');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('jenis_surat');
    }
};
