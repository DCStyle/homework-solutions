<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIGenerationHistory extends Model
{
    use HasFactory;

    protected $table = 'ai_generation_history';

    protected $fillable = [
        'user_id',
        'content_type',
        'filter_type',
        'filter_id',
        'prompt_text',
        'model',
        'total_items',
        'successful_items',
        'failed_items',
        'error_messages',
        'settings',
        'processed_items',
        'status'
    ];

    protected $casts = [
        'settings' => 'json',
        'processed_items' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that initiated the generation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the success rate as a percentage.
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_items == 0) {
            return 0;
        }
        
        return round(($this->successful_items / $this->total_items) * 100);
    }
}
