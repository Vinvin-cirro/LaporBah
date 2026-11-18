<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('duplicate_of_report_id')
                ->nullable()
                ->after('area_id')
                ->constrained('reports')
                ->nullOnDelete();
        });

        Schema::table('report_updates', function (Blueprint $table) {
            $table->string('action', 30)->nullable()->after('new_status');
        });
    }

    public function down(): void
    {
        Schema::table('report_updates', function (Blueprint $table) {
            $table->dropColumn('action');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicate_of_report_id');
        });
    }
};
