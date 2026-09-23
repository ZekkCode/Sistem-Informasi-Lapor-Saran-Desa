<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->uuid('token_kirim')->nullable()->unique();
            $table->unsignedInteger('versi_alur')->default(0);
            $table->string('nama_pengusul', 120)->nullable();
            $table->string('telepon_pengusul', 30)->nullable();
            $table->foreignId('dusun_id')->constrained('dusuns')->restrictOnDelete();
            $table->string('jenis', 32);
            $table->string('judul', 120);
            $table->text('isi');
            $table->string('status', 32)->default('baru');
            $table->boolean('tampil_publik')->default(false);
            $table->text('catatan_publik')->nullable();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dipublikasikan_pada')->nullable();
            $table->timestamp('dikirim_pada')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index('dikirim_pada');
            $table->index('status');
            $table->index('tampil_publik');
            $table->index(['tampil_publik', 'status', 'dikirim_pada'], 'usulan_daftar_publik_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan');
    }
};
