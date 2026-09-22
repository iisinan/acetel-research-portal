<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('matric_no')->unique();
            $table->foreignId('programme_id')->constrained();
            $table->foreignId('degree_id')->constrained();
            $table->foreignId('cohort_id')->constrained();
            $table->year('admission_year');
            $table->string('phone')->nullable();
            $table->string('registration_status')->default('Pending');
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};