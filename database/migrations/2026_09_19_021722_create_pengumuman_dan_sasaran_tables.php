<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('pembuat_id')->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $t->string('judul', 200);
            $t->text('isi');
            $t->string('status', 20)->default('draf');
            $t->dateTime('terbit_at')->nullable();
            $t->dateTime('berakhir_at')->nullable();
            $t->dateTime('diarsipkan_at')->nullable();
            $t->unsignedInteger('revisi')->default(1);
            $t->uuid('form_token');
            $t->char('hash_permohonan', 64);
            $t->timestamps();
            $t->unique(['pembuat_id', 'form_token'], 'pengumuman_token_unik');
            $t->index(['status', 'terbit_at']);
            $t->index(['pembuat_id', 'status']);
        });
        Schema::create('sasaran_pengumuman', function (Blueprint $t): void {
            $t->engine = 'InnoDB';
            $t->id();
            $t->foreignId('pengumuman_id')->constrained('pengumuman')->restrictOnDelete()->restrictOnUpdate();
            $t->string('lingkup', 20);
            $t->foreignId('program_studi_id')->nullable()->constrained('program_studi')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('kelas_kuliah_id')->nullable()->constrained('kelas_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete()->restrictOnUpdate();
            // Kunci kanonis mengatasi NULL pada composite unique biasa.
            $t->char('kunci_sasaran', 64);
            $t->unique(['pengumuman_id', 'kunci_sasaran'], 'pengumuman_sasaran_unik');
            $t->index(['lingkup', 'program_studi_id']);
            $t->index(['lingkup', 'kelas_kuliah_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('sasaran_pengumuman');
        Schema::dropIfExists('pengumuman');
    }
};
