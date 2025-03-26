<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default settings
        $defaultSettings = [
            'default_ai_provider' => 'openai',
            'vector_search_enabled' => true,
            'vector_search_threshold' => 0.7,
            'moderation_enabled' => true,
            'auto_approve_questions' => false,
            'max_questions_per_day' => 10,
            'max_comments_per_day' => 30,
        ];
        
        foreach ($defaultSettings as $key => $value) {
            DB::table('wiki_settings')->insert([
                'key' => $key,
                'value' => is_bool($value) ? (int) $value : $value,
                'group' => 'general',
                'description' => 'Default setting',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default settings added by this migration
        DB::table('wiki_settings')->whereIn('key', [
            'default_ai_provider',
            'vector_search_enabled',
            'vector_search_threshold',
            'moderation_enabled',
            'auto_approve_questions',
            'max_questions_per_day',
            'max_comments_per_day',
        ])->delete();
    }
}; 