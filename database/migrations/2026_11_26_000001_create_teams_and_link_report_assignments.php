<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('institution_name', 150)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name']);
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::table('report_assignments', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('officer_id')
                ->constrained('teams')->nullOnDelete();
            $table->index(['team_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropIndex(['team_id', 'ended_at']);
            $table->dropColumn('team_id');
        });
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
