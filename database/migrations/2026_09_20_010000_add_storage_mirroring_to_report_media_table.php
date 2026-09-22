<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_media', function (Blueprint $table) {
            $table->string('storage_disk', 50)->default('public')->after('media_type');
            $table->string('google_drive_status', 20)->nullable()->after('uploaded_by');
            $table->string('google_drive_file_id')->nullable()->after('google_drive_status');
            $table->unsignedTinyInteger('google_drive_attempts')->default(0)->after('google_drive_file_id');
            $table->timestamp('google_drive_synced_at')->nullable()->after('google_drive_attempts');
            $table->text('google_drive_error')->nullable()->after('google_drive_synced_at');

            $table->index('google_drive_status');
        });
    }

    public function down(): void
    {
        Schema::table('report_media', function (Blueprint $table) {
            $table->dropIndex(['google_drive_status']);
            $table->dropColumn([
                'storage_disk',
                'google_drive_status',
                'google_drive_file_id',
                'google_drive_attempts',
                'google_drive_synced_at',
                'google_drive_error',
            ]);
        });
    }
};
