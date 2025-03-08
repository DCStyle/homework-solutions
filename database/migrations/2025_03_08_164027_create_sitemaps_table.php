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
        Schema::create('sitemaps', function (Blueprint $table) {
            $table->id();
            $table->string('loc', 500)->unique(); // URL path - might be long for some URLs
            $table->string('type', 50); // e.g., 'post', 'article', 'category'
            $table->foreignId('model_id')->nullable(); // Reference to the original model ID
            $table->timestamp('lastmod'); // Last modified date
            $table->string('changefreq', 20)->default('weekly'); // Change frequency
            $table->decimal('priority', 3, 1)->default(0.5); // Priority (0.1 to 1.0)
            $table->integer('sitemap_index')->default(1); // Which sub-sitemap this belongs to
            $table->timestamps();
            
            // Indexes for performance
            $table->index('type');
            $table->index(['type', 'sitemap_index']);
            $table->index(['type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitemaps');
    }
};
