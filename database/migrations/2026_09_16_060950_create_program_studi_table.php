<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->string('jenjang', 30);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['aktif', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};
