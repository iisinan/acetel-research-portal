<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('student_milestones', 'is_supervisor_approved')) {
                $table->boolean('is_supervisor_approved')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_milestones', function (Blueprint $table) {
            if (Schema::hasColumn('student_milestones', 'is_supervisor_approved')) {
                $table->dropColumn('is_supervisor_approved');
            }
        });
    }
};
