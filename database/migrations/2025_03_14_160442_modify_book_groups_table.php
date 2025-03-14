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
        // Increase length for column 'description' in 'book_groups' table
        Schema::table('book_groups', function (Blueprint $table) {
            $table->longText('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Decrease length for column 'description' in 'book_groups' table
        Schema::table('book_groups', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }
};
