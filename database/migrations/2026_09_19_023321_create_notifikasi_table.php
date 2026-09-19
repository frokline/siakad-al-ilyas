<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('penerima_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->string('jenis', 30);
            $t->string('judul', 200);
            $t->string('sumber_tabel', 60);
            $t->unsignedBigInteger('sumber_id');
            $t->unsignedInteger('sumber_revisi');
            $t->string('kunci_peristiwa', 190);
            $t->dateTime('dibaca_at')->nullable();
            $t->timestamps();
            $t->unique(['penerima_id', 'kunci_peristiwa'], 'notifikasi_penerima_peristiwa_unik');
            $t->index(['penerima_id', 'dibaca_at', 'id'], 'notifikasi_kotak_masuk');
            $t->index(['sumber_tabel', 'sumber_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
