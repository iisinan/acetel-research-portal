<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Institutional Mandate: Supervisors cannot schedule presentations, only Admin can
        DB::table('milestone_templates')
            ->where('defence_date_role', 'Supervisor')
            ->update(['defence_date_role' => 'Admin']);

        DB::table('milestone_templates')
            ->whereIn('slug', ['proposal_defence', 'proposal'])
            ->update(['defence_date_role' => 'Admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert not required
    }
};
