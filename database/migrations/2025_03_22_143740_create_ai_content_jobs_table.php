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
        Schema::create('ai_content_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->index(); // For grouping jobs
            $table->unsignedBigInteger('user_id'); // Job creator
            $table->string('content_type'); // posts, chapters, books, book_groups
            $table->unsignedInteger('total_items');
            $table->unsignedInteger('processed_items')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status'); // pending, processing, completed, failed, cancelled
            $table->text('error_message')->nullable();
            $table->json('settings'); // Store model, prompt, temperature, etc.
            $table->json('item_ids'); // Store IDs of items to process
            $table->json('failed_items')->nullable(); // For retry functionality
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_content_jobs');
    }
};
