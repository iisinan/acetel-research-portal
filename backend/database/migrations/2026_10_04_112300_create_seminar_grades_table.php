<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("seminar_grades", function (Blueprint $table) {
            $table->id();
            $table->foreignId("student_milestone_id")->constrained("student_milestones")->onDelete("cascade");
            $table->foreignId("examiner_id")->constrained("users")->onDelete("cascade");
            $table->decimal("grade", 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("seminar_grades");
    }
};

