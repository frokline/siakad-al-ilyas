<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_keluarga', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('mahasiswa_id')
                ->constrained('mahasiswa')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            // AYAH, IBU, ATAU WALI
            $table->enum('jenis_hubungan', [
                'ayah',
                'ibu',
                'wali',
            ]);

            $table->string('nama_lengkap', 150)->nullable();
            $table->string('nik', 32)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('status_hidup', 30)->nullable();
            $table->string('status_kekerabatan', 60)->nullable();
            $table->string('pendidikan_terakhir', 100)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->unsignedBigInteger('penghasilan')->nullable();

            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('email', 190)->nullable();

            $table->timestamps();

            // Satu data untuk setiap peran keluarga per mahasiswa.
            $table->unique(
                ['mahasiswa_id', 'jenis_hubungan'],
                'mhs_keluarga_peran_unique'
            );

            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_keluarga');
    }
};
