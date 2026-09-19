<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_akademik', function (Blueprint $table): void {
            $table->id();

            $table->string('kode', 30)->unique();
            $table->unsignedSmallInteger('tahun_mulai');

            $table->enum('jenis', [
                'ganjil',
                'genap',
                'pendek',
            ]);

            $table->date('mulai');
            $table->date('selesai');

            $table->dateTime('krs_mulai')->nullable();
            $table->dateTime('krs_selesai')->nullable();

            $table->enum('status', [
                'persiapan',
                'aktif',
                'arsip',
            ])->default('persiapan');

            $table->timestamps();

            $table->unique(['tahun_mulai', 'jenis']);
            $table->index(['status', 'mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_akademik');
    }
};
