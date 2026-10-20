<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_pendidikan_asal', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('mahasiswa_id')
                ->unique()
                ->constrained('mahasiswa')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            // DATA PENDIDIKAN ASAL
            $table->string('jenis_pendidikan_asal', 100)->nullable();
            $table->string('perguruan_tinggi', 190)->nullable();
            $table->string('program_studi_asal', 190)->nullable();
            $table->decimal('ipk', 3, 2)->nullable();
            $table->unsignedSmallInteger('sks_diakui')->nullable();
            $table->string('nomor_ijazah', 100)->nullable();
            $table->string('nisn', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_pendidikan_asal');
    }
};
