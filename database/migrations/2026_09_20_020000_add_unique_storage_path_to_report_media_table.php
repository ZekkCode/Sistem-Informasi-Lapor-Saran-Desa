<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_media', function (Blueprint $table) {
            $table->unique(['storage_disk', 'file_path'], 'report_media_disk_path_unique');
        });
    }

    public function down(): void
    {
        Schema::table('report_media', function (Blueprint $table) {
            $table->dropUnique('report_media_disk_path_unique');
        });
    }
};
