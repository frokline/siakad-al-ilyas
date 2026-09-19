<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pelaku_id')->nullable()
                ->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->string('entitas', 60);
            $table->unsignedBigInteger('entitas_id');
            $table->unsignedInteger('versi_entitas')->nullable();
            $table->string('aksi', 40);
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->text('alasan')->nullable();
            $table->dateTime('waktu');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entitas', 'entitas_id', 'waktu'], 'audit_entitas_waktu_index');
            $table->unique(['entitas', 'entitas_id', 'versi_entitas'], 'audit_entitas_versi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
