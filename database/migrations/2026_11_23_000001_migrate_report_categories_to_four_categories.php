<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize legacy report categories while preserving every report row.
     */
    public function up(): void
    {
        DB::table('reports')->where('category', 'begal')->update(['category' => 'begal_curas']);
        DB::table('reports')->where('category', 'dark')->update(['category' => 'area_gelap']);
    }

    /**
     * Keep canonical values on rollback because reverse mapping is lossy.
     */
    public function down(): void
    {
        // Intentionally no-op: once reports use the four-category vocabulary,
        // mapping them back would lose distinctions (PJU Rusak vs Area Gelap,
        // and Lain-lain). Keep report data intact on rollback.
    }
};
