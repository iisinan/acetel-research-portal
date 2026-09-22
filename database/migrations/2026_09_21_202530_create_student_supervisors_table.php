<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_supervisor', function (Blueprint $table) {

            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // Principal, Co-Supervisor
            $table->string('status')->default('Active');
            $table->timestamp('assigned_date')->useCurrent();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_supervisor');
    }
};