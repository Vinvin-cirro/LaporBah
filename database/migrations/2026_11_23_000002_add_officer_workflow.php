<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->string('status', 30)->default('assigned')->after('priority');
            $table->timestamp('accepted_at')->nullable()->after('assigned_at');
            $table->timestamp('started_at')->nullable()->after('accepted_at');
            $table->timestamp('completion_requested_at')->nullable()->after('started_at');
            $table->timestamp('completed_at')->nullable()->after('completion_requested_at');
        });

        Schema::create('officer_task_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 50);
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['report_assignment_id', 'created_at']);
        });

        Schema::create('officer_task_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 50);
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('officer_patrol_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('patrol_date');
            $table->time('started_at');
            $table->time('ended_at')->nullable();
            $table->string('location');
            $table->text('monitoring_result');
            $table->text('findings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['report_assignment_id', 'patrol_date']);
        });

        Schema::create('officer_patrol_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_log_id')->constrained('officer_patrol_logs')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_patrol_images');
        Schema::dropIfExists('officer_patrol_logs');
        Schema::dropIfExists('officer_task_documents');
        Schema::dropIfExists('officer_task_updates');
        Schema::table('report_assignments', function (Blueprint $table) {
            $table->dropColumn(['status', 'accepted_at', 'started_at', 'completion_requested_at', 'completed_at']);
        });
    }
};
