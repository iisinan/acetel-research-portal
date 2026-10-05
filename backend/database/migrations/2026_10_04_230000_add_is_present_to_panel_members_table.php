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
        if (Schema::hasTable('panel_members') && !Schema::hasColumn('panel_members', 'is_present')) {
            Schema::table('panel_members', function (Blueprint $table) {
                $table->boolean('is_present')->default(false)->after('invitation_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('panel_members') && Schema::hasColumn('panel_members', 'is_present')) {
            Schema::table('panel_members', function (Blueprint $table) {
                $table->dropColumn('is_present');
            });
        }
    }
};
