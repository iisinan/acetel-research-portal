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
            $table->string('publication_title')->nullable()->after('type');
            $table->string('doi')->nullable()->after('publication_title');
            $table->boolean('is_scopus_indexed')->nullable()->after('doi');
            $table->json('scopus_data')->nullable()->after('is_scopus_indexed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['publication_title', 'doi', 'is_scopus_indexed', 'scopus_data']);
        });
    }
};
