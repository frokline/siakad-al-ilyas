<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_alamat', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('mahasiswa_id')
                ->unique()
                ->constrained('mahasiswa')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            // ALAMAT KTP
            $table->text('alamat_ktp')->nullable();
            $table->string('rt_ktp', 5)->nullable();
            $table->string('rw_ktp', 5)->nullable();
            $table->string('dusun_ktp', 100)->nullable();
            $table->string('desa_kelurahan_ktp', 100)->nullable();
            $table->string('provinsi_ktp', 100)->nullable();
            $table->string('kota_kabupaten_ktp', 100)->nullable();
            $table->string('kecamatan_ktp', 100)->nullable();
            $table->string('kode_pos_ktp', 10)->nullable();

            // ALAMAT DOMISILI
            $table->boolean('domisili_sama_dengan_ktp')
                ->default(false);

            $table->text('alamat_domisili')->nullable();
            $table->string('rt_domisili', 5)->nullable();
            $table->string('rw_domisili', 5)->nullable();
            $table->string('dusun_domisili', 100)->nullable();
            $table->string('desa_kelurahan_domisili', 100)->nullable();
            $table->string('provinsi_domisili', 100)->nullable();
            $table->string('kota_kabupaten_domisili', 100)->nullable();
            $table->string('kecamatan_domisili', 100)->nullable();
            $table->string('kode_pos_domisili', 10)->nullable();
            $table->string('status_tinggal', 50)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_alamat');
    }
};
