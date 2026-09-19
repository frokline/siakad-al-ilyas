<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('tagihan_id')->constrained('tagihan')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('pengunggah_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('bukti_berkas_id')->constrained('berkas')->restrictOnDelete()->restrictOnUpdate();
            $t->string('nomor_pengajuan', 60)->unique();
            $t->decimal('nominal_diajukan', 14, 2);
            $t->date('tanggal_transfer');
            $t->string('referensi_bank', 100)->nullable();
            $t->string('tujuan_transfer', 150);
            $t->json('tagihan_snapshot');
            $t->json('bukti_snapshot');
            $t->char('bukti_sha256', 64)->index();
            $t->string('status', 20)->default('menunggu');
            // NULL boleh berulang. Menunggu/diterima sama-sama menempati slot unik.
            $t->unsignedBigInteger('tagihan_aktif_id')->nullable()->unique('pembayaran_slot_tagihan_unique');
            $t->char('bukti_aktif_sha256', 64)->nullable()->unique('pembayaran_slot_bukti_unique');
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->unsignedInteger('revisi')->default(1);
            $t->dateTime('diajukan_at');
            $t->dateTime('dibatalkan_at')->nullable();
            $t->text('alasan_batal')->nullable();
            $t->timestamps();
            $t->unique(['pengunggah_id', 'form_token'], 'pembayaran_form_unique');
            $t->index(['tagihan_id', 'status', 'id']);
            $t->index(['status', 'diajukan_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
