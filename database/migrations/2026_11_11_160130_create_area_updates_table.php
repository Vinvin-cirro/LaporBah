
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_updates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('area_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);

            $table->text('action_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_updates');
    }
};
