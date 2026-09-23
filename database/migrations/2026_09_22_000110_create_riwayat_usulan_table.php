<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_usulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usulan_id')->constrained('usulan')->cascadeOnDelete();
            $table->string('status', 32);
            $table->text('catatan_publik')->nullable();
            $table->text('catatan_internal')->nullable();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_usulan');
    }
};
