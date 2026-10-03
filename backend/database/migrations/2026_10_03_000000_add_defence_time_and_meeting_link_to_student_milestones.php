<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('student_milestones', 'defence_time')) {
                $table->string('defence_time', 10)->nullable()->after('defence_date');
            }
            if (!Schema::hasColumn('student_milestones', 'meeting_link')) {
                $table->string('meeting_link', 500)->nullable()->after('defence_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            $table->dropColumn(['defence_time', 'meeting_link']);
        });
    }
};
