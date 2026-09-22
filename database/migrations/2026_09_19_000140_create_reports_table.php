<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 30)->unique();
            $table->string('reporter_name', 120);
            $table->string('reporter_phone', 30);
            $table->foreignId('dusun_id')->constrained()->restrictOnDelete();
            $table->foreignId('subcategory_id')->constrained()->restrictOnDelete();
            $table->foreignId('qr_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 120);
            $table->text('description');
            $table->string('location_text', 255);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('citizen_priority', 32);
            $table->string('admin_priority', 32)->nullable();
            $table->string('verification_status', 32)->default('pending');
            $table->string('status', 32)->default('not_started');
            $table->text('current_public_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index('submitted_at');
            $table->index('status');
            $table->index('verification_status');
            $table->index(['verification_status', 'status', 'submitted_at'], 'reports_public_listing_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
