<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'audit_log'] as $induk) {
            if (! Schema::hasTable($induk)) {
                throw new RuntimeException("Tabel induk {$induk} belum tersedia.");
            }
        }
        Schema::create('berkas', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('diunggah_oleh')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->uuid('upload_token');
            $table->string('storage_disk', 40);
            $table->string('object_key', 500);
            $table->string('nama_asli', 255);
            $table->string('label', 200);
            $table->text('keterangan')->nullable();
            $table->string('mime_type', 150);
            $table->string('ekstensi', 10);
            $table->unsignedBigInteger('ukuran_byte');
            $table->char('sha256', 64);
            $table->enum('pemeriksaan', ['format', 'clamav']);
            $table->dateTime('diperiksa_at');
            $table->enum('status', ['menunggu', 'tersedia', 'ditolak', 'dihapus'])->default('menunggu');
            $table->string('pesan_status', 255)->nullable();
            $table->dateTime('tersedia_at')->nullable();
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
            $table->unique(['diunggah_oleh', 'upload_token'], 'berkas_upload_unique');
            $table->unique(['storage_disk', 'object_key'], 'berkas_object_unique');
            $table->index(['diunggah_oleh', 'status', 'id'], 'berkas_pemilik_status_index');
            $table->index(['status', 'created_at'], 'berkas_proses_index');
        });
        DB::statement("ALTER TABLE berkas ADD CONSTRAINT berkas_metadata_valid CHECK (
            ukuran_byte > 0 AND revisi > 0 AND
            ((status IN ('menunggu', 'ditolak') AND tersedia_at IS NULL AND dinonaktifkan_at IS NULL)
             OR (status = 'tersedia' AND tersedia_at IS NOT NULL AND dinonaktifkan_at IS NULL)
             OR (status = 'dihapus' AND tersedia_at IS NOT NULL AND dinonaktifkan_at IS NOT NULL))
        )");
    }
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
