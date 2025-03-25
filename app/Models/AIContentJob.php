<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIContentJob extends Model
{
    use HasFactory;

    protected $table = 'ai_content_jobs';
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'item_ids' => 'array',
        'failed_items' => 'array',
    ];

    public static $JOB_STATUS_PENDING = 'pending';
    public static $JOB_STATUS_PROCESSING = 'processing';
    public static $JOB_STATUS_COMPLETED = 'completed';
    public static $JOB_STATUS_FAILED = 'failed';
    public static $JOB_STATUS_REPLACED = 'replaced';
    public static $JOB_STATUS_CANCELLED = 'cancelled';

    /**
     * Get human-readable status
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'Đang chờ',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'failed' => 'Thất bại',
            'replaced' => 'Đã thay thế',
            'cancelled' => 'Đã hủy',
            default => $this->status
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_items == 0) return 0;
        return round(($this->processed_items / $this->total_items) * 100);
    }

    /**
     * Retry failed items in the job
     *
     * @return int|bool The ID of the new job if created, or false if no failed items
     */
    public function retryFailedItems()
    {
        if (empty($this->failed_items) || count($this->failed_items) === 0) {
            return false;
        }

        // Extract IDs from failed items
        $failedIds = collect($this->failed_items)->pluck('id')->toArray();

        if (empty($failedIds)) {
            return false;
        }

        // Create a new job for just the failed items
        $retryJob = new self([
            'batch_id' => $this->batch_id . '-retry-' . now()->timestamp,
            'user_id' => $this->user_id,
            'content_type' => $this->content_type,
            'total_items' => count($failedIds),
            'processed_items' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'pending',
            'settings' => $this->settings,
            'item_ids' => $failedIds,
        ]);

        $retryJob->save();

        // Dispatch a new job
        \App\Jobs\ProcessAIContentBatch::dispatch($retryJob->id);

        return $retryJob->id;
    }
}
