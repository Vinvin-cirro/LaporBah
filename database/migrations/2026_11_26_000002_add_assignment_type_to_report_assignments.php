<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->string('assignment_type', 30)->default('handling')->after('team_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->dropIndex(['assignment_type']);
            $table->dropColumn('assignment_type');
        });
    }
};
