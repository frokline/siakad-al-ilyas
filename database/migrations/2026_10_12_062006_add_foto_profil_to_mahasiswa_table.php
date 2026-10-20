<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table): void {
            $table->string('foto_profil', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table): void {
            $table->dropColumn('foto_profil');
        });
    }
};
