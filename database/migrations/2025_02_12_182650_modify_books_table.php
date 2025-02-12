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
        // First, make the column nullable to avoid constraints during conversion
        Schema::table('books', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
        });

        // Then convert to longText
        Schema::table('books', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, make the column nullable to avoid constraints during conversion
        Schema::table('books', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
        });

        // Then convert to longText
        Schema::table('books', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
        });
    }
};
