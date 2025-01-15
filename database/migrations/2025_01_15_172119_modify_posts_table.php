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
        // Increase the length of 'title' column in 'posts' table to 500 characters
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title', 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Decrease the length of 'title' column in 'posts' table to 255 characters
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title', 255)->change();
        });
    }
};
