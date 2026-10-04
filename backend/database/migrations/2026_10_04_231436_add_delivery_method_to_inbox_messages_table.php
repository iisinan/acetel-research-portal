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
        Schema::table('inbox_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('inbox_messages', 'delivery_method')) {
                $table->enum('delivery_method', ['in_app', 'email', 'both'])->default('both')->after('body');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbox_messages', function (Blueprint $table) {
            if (Schema::hasColumn('inbox_messages', 'delivery_method')) {
                $table->dropColumn('delivery_method');
            }
        });
    }
};
