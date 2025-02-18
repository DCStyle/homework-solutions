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
        // Add column "source_url" to "posts" table
        Schema::table('posts', function (Blueprint $table) {
            $table->string('source_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop column "source_url" from "posts" table
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
    }
};
