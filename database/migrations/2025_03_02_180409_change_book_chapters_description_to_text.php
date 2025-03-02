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
        Schema::table('book_chapters', function (Blueprint $table) {
            // Change description from string (VARCHAR) to text to allow for longer content
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_chapters', function (Blueprint $table) {
            // Change back to string if needed
            $table->string('description')->nullable()->change();
        });
    }
};
