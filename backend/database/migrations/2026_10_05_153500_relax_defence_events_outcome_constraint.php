<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop Postgres check constraint on outcome if it exists
        try {
            DB::statement('ALTER TABLE defence_events DROP CONSTRAINT IF EXISTS defence_events_outcome_check');
        } catch (\Throwable $e) {}

        // 2. Make outcome nullable with default 'pending'
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement('ALTER TABLE defence_events ALTER COLUMN outcome DROP NOT NULL');
                DB::statement("ALTER TABLE defence_events ALTER COLUMN outcome SET DEFAULT 'pending'");
            } catch (\Throwable $e) {}
        } else {
            try {
                Schema::table('defence_events', function (Blueprint $table) {
                    $table->string('outcome')->nullable()->default('pending')->change();
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
