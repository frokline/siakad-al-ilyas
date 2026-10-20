<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_biodata', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('mahasiswa_id')
                ->unique()
                ->constrained('mahasiswa')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            // INFORMASI UMUM
            $table->string('agama', 30)->nullable();
            $table->string('suku', 100)->nullable();
            $table->unsignedTinyInteger('berat_badan')->nullable();
            $table->unsignedSmallInteger('tinggi_badan')->nullable();
            $table->string('golongan_darah', 3)->nullable();
            $table->string('transportasi', 50)->nullable();

            // ADMINISTRASI
            $table->string('kewarganegaraan', 60)->nullable();
            $table->string('nik', 32)->nullable()->unique();
            $table->string('nomor_kk', 32)->nullable();
            $table->string('npwp', 32)->nullable();
            $table->string('nomor_kps', 32)->nullable();
            $table->string('status_nikah', 30)->nullable();
            $table->string('ukuran_jas_almamater', 10)->nullable();

            // KONTAK TAMBAHAN
            $table->string('nomor_hp', 30)->nullable();
            $table->string('email_pribadi', 190)->nullable();

            // PEKERJAAN
            $table->string('pekerjaan', 100)->nullable();
            $table->string('instansi_pekerjaan', 190)->nullable();
            $table->unsignedBigInteger('penghasilan')->nullable();

            // INFORMASI BANK
            $table->string('nomor_rekening', 50)->nullable();
            $table->string('nama_rekening', 150)->nullable();
            $table->string('nama_bank', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_biodata');
    }
};
