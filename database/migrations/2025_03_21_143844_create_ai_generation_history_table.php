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
        Schema::create('ai_generation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('content_type'); // posts, chapters, books, book_groups
            $table->string('filter_type')->nullable();
            $table->unsignedBigInteger('filter_id')->nullable();
            $table->text('prompt_text');
            $table->string('model');
            $table->integer('total_items')->default(0);
            $table->integer('successful_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->text('error_messages')->nullable();
            $table->json('settings')->nullable(); // temperature, max_tokens, etc.
            $table->json('processed_items')->nullable(); // list of item IDs that were processed
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generation_history');
    }
};
