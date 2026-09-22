<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reports', 'workflow_version')) {
            return;
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedInteger('workflow_version')->default(0)->after('submission_token');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('reports', 'workflow_version')) {
            return;
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('workflow_version');
        });
    }
};
