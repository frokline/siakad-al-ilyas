<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'mahasiswa', 'detail_krs', 'pertemuan', 'audit_log'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Jalankan migration induk terlebih dahulu: {$table}.");
            }
        }

        Schema::table('detail_krs', function (Blueprint $table): void {
            $table->unique(['id', 'kelas_kuliah_id'], 'detail_krs_identitas_kelas_unique');
        });

        Schema::create('presensi_pertemuan', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('pertemuan_id')->unique();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->enum('status', ['terbuka', 'ditutup'])->default('terbuka');
            $table->unsignedInteger('jumlah_peserta');
            $table->foreignId('dibuka_oleh')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('dibuka_at');
            $table->foreignId('ditutup_oleh')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('ditutup_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
            $table->unique(['id', 'kelas_kuliah_id'], 'presensi_pertemuan_identitas_unique');
            $table->foreign(['pertemuan_id', 'kelas_kuliah_id'], 'presensi_pertemuan_sesi_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('pertemuan')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('presensi', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('presensi_pertemuan_id');
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('detail_krs_id');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->restrictOnDelete()->restrictOnUpdate();
            $table->json('peserta_snapshot');
            $table->enum('status', ['belum_dicatat', 'hadir', 'izin', 'sakit', 'alpa'])->default('belum_dicatat');
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('dicatat_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
            $table->unique(['presensi_pertemuan_id', 'detail_krs_id'], 'presensi_detail_unique');
            $table->unique(['presensi_pertemuan_id', 'mahasiswa_id'], 'presensi_mahasiswa_unique');
            $table->index(['presensi_pertemuan_id', 'status'], 'presensi_status_index');
            $table->foreign(['presensi_pertemuan_id', 'kelas_kuliah_id'], 'presensi_daftar_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('presensi_pertemuan')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['detail_krs_id', 'kelas_kuliah_id'], 'presensi_detail_kelas_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('detail_krs')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        DB::statement("ALTER TABLE presensi_pertemuan ADD CONSTRAINT presensi_pertemuan_valid CHECK (
            jumlah_peserta > 0 AND revisi > 0 AND
            ((status = 'terbuka' AND ditutup_oleh IS NULL AND ditutup_at IS NULL)
            OR (status = 'ditutup' AND ditutup_oleh IS NOT NULL AND ditutup_at IS NOT NULL AND ditutup_at >= dibuka_at))
        )");
        DB::statement("ALTER TABLE presensi ADD CONSTRAINT presensi_pencatatan_valid CHECK (
            revisi > 0 AND
            ((status = 'belum_dicatat' AND dicatat_oleh IS NULL AND dicatat_at IS NULL AND catatan IS NULL)
            OR (status <> 'belum_dicatat' AND dicatat_oleh IS NOT NULL AND dicatat_at IS NOT NULL))
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
        Schema::dropIfExists('presensi_pertemuan');
        Schema::table('detail_krs', function (Blueprint $table): void {
            $table->dropUnique('detail_krs_identitas_kelas_unique');
        });
    }
};
