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
        Schema::create('wiki_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('wiki_questions')->onDelete('cascade');
            $table->text('content');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->boolean('is_ai')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wiki_answers');
    }
}; 