<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('tracking_code', 64)
                ->nullable()
                ->unique();

            $table->timestamp('incident_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropUnique(['tracking_code']);
            $table->dropColumn([
                'tracking_code',
                'incident_at',
            ]);
        });
    }
};
