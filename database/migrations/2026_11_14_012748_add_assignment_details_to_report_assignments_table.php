
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->string('institution_name', 150)->nullable()
                ->after('officer_id');

            $table->string('team_name', 150)->nullable()
                ->after('institution_name');

            $table->string('priority', 20)->default('normal')
                ->after('team_name');

            $table->timestamp('due_at')->nullable()
                ->after('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'institution_name',
                'team_name',
                'priority',
                'due_at',
            ]);
        });
    }
};
