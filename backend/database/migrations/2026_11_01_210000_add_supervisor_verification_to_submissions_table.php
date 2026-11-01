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
        Schema::table('submissions', function (Blueprint $table) {
            $table->boolean('has_supervisor_coauthor')->default(false)->after('is_scopus_indexed');
            $table->string('matched_supervisor_name')->nullable()->after('has_supervisor_coauthor');
            $table->json('publication_authors')->nullable()->after('matched_supervisor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['has_supervisor_coauthor', 'matched_supervisor_name', 'publication_authors']);
        });
    }
};
