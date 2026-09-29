<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Submissions
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->foreign('submitted_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        // 2. Messages
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        // 3. Audit Logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // 4. Evaluations
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['evaluator_id']);
            $table->foreign('evaluator_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Submissions
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->foreign('submitted_by')->references('id')->on('users');
        });

        // Messages
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Audit Logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Evaluations
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['evaluator_id']);
            $table->foreign('evaluator_id')->references('id')->on('users');
        });
    }
};
