<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();

            // Titik tengah kelompok lokasi
            $table->decimal('center_latitude', 10, 7);
            $table->decimal('center_longitude', 10, 7);

            // Status pemantauan area
            $table->string('status', 20)->default('monitoring');

            $table->timestamp('last_reported_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }

};
