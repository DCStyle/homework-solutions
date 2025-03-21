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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('prompt_text');
            $table->enum('content_type', ['posts', 'chapters', 'books', 'book_groups']);
            $table->string('ai_model')->nullable()->comment('Specific AI model this prompt is designed for');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable()->comment('Description or notes about the prompt');
            $table->text('system_message')->nullable()->comment('System message for context (primarily for DeepSeek)');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
