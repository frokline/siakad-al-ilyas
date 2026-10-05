<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'verifikasi_pembayaran',
            function (Blueprint $table): void {
                $table->engine = 'InnoDB';

                $table->id();

                $table->foreignId('pembayaran_id')
                    ->constrained('pembayaran')
                    ->restrictOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('petugas_id')
                    ->constrained('users')
                    ->restrictOnUpdate()
                    ->restrictOnDelete();

                $table->string('tindakan', 20);

                $table->string('status_sebelum', 20);
                $table->string('status_sesudah', 20);

                $table->text('catatan');

                $table->unsignedInteger('revisi_pembayaran');

                $table->dateTime('waktu');

                $table->timestamps();

                $table->unique(
                    ['pembayaran_id', 'revisi_pembayaran'],
                    'verifikasi_pembayaran_revisi_unique'
                );

                $table->index(
                    ['pembayaran_id', 'waktu'],
                    'verifikasi_pembayaran_waktu_index'
                );

                $table->index(
                    ['petugas_id', 'waktu'],
                    'verifikasi_pembayaran_petugas_index'
                );

                $table->index(
                    ['tindakan', 'waktu'],
                    'verifikasi_pembayaran_tindakan_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_pembayaran');
    }
};