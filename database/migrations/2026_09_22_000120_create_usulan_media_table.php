<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usulan_id')->constrained('usulan')->cascadeOnDelete();
            $table->string('storage_disk', 32)->default('public');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_media');
    }
};
