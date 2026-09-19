<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('mahasiswa_id')->constrained('mahasiswa')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('registrasi_semester_id')->constrained('registrasi_semester')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('jenis_biaya_id')->constrained('jenis_biaya')->restrictOnDelete()->restrictOnUpdate();
            $t->string('nomor', 60)->unique();
            $t->unsignedSmallInteger('tahun_tagihan');
            $t->unsignedTinyInteger('bulan_tagihan');
            $t->decimal('nominal', 14, 2);
            $t->date('jatuh_tempo');
            $t->string('status', 20)->default('draf');
            $t->json('snapshot')->nullable();
            $t->text('catatan')->nullable();
            $t->dateTime('diterbitkan_at')->nullable();
            $t->dateTime('dibatalkan_at')->nullable();
            $t->foreignId('pembuat_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->unsignedInteger('revisi')->default(1);
            $t->timestamps();
            $t->unique(['mahasiswa_id', 'jenis_biaya_id', 'tahun_tagihan', 'bulan_tagihan'], 'tagihan_bulan_unique');
            $t->unique(['pembuat_id', 'form_token'], 'tagihan_form_unique');
            $t->index(['status', 'jatuh_tempo']);
            $t->index(['registrasi_semester_id', 'status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
