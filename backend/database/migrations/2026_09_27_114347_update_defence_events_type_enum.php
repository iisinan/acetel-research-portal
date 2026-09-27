<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the check constraint if it exists (Postgres creates check constraints for enum columns sometimes)
        try {
            DB::statement('ALTER TABLE defence_events DROP CONSTRAINT IF EXISTS defence_events_type_check');
        } catch (\Exception $e) {
            // Ignore if it doesn't exist or not postgres
        }
    }

    public function down(): void
    {
        //
    }
};
