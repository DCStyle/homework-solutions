# Background Task Queue for AI Content Generation

## Problem Statement
Currently, the AI dashboard's bulk content generation process runs synchronously, causing HTTP timeouts for large batches. Users are unable to navigate away during processing, and the system is prone to failures for time-consuming operations.

## Proposed Solution
Implement a Laravel Queue-based background processing system that will:
1. Process AI content generation in the background
2. Allow users to navigate away and check back later
3. Track job progress and status
4. Provide notifications on completion

## Implementation Steps

### 1. Database Structure for Job Tracking

Create a migration for tracking AI generation jobs:

```php
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
            $table->string('status'); // pending, processing, completed, failed
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
```

### 2. Create Model for Job Tracking

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIContentJob extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'settings' => 'array',
        'item_ids' => 'array',
        'failed_items' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getProgressPercentageAttribute()
    {
        if ($this->total_items == 0) return 0;
        return round(($this->processed_items / $this->total_items) * 100);
    }
}
```

### 3. Create Background Job Class

```php
<?php

namespace App\Jobs;

use App\Models\AIContentJob;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\OpenRouterResponseFormatter;
use Illuminate\Support\Facades\Log;

class ProcessAIContentBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    protected $jobId;

    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }

    public function handle(AIService $aiService)
    {
        $job = AIContentJob::findOrFail($this->jobId);
        $job->status = 'processing';
        $job->save();

        $failed = [];
        
        try {
            foreach ($job->item_ids as $index => $itemId) {
                // Add rate limiting delay to prevent API throttling
                if ($index > 0) {
                    usleep(500000); // 500ms delay between requests
                }
                
                try {
                    // Get content item
                    $item = $this->getContentObject($job->content_type, $itemId);
                    
                    if (!$item) {
                        $failed[] = [
                            'id' => $itemId,
                            'error' => 'Item not found'
                        ];
                        $job->failed_count++;
                        continue;
                    }
                    
                    // Prepare prompt
                    $prompt = $this->preparePrompt($job->settings['prompt'], $item, $job->content_type);
                    
                    // AI generation options
                    $options = [
                        'content_type' => $job->content_type,
                        'max_tokens' => $job->settings['max_tokens'] ?? 4000,
                        'temperature' => $job->settings['temperature'] ?? 0.7,
                    ];
                    
                    // Add model-specific parameters
                    if (str_starts_with($job->settings['model'], 'deepseek')) {
                        $options['system_message'] = $job->settings['system_message'] ?? '';
                        $options['model_variant'] = $job->settings['model_variant'] ?? 'deepseek-chat';
                    }
                    
                    // Generate content
                    $result = $aiService->generate(
                        $job->settings['model'],
                        $prompt,
                        $options,
                        $job->settings['use_html_meta'] ?? false
                    );
                    
                    // Format the result with the helper to handle HTML entities
                    if ($job->content_type === 'posts' && is_array($result)) {
                        if (isset($result['meta_title'])) {
                            $result['meta_title'] = OpenRouterResponseFormatter::formatResponse($result['meta_title'], false);
                        }
                        if (isset($result['meta_description'])) {
                            $result['meta_description'] = OpenRouterResponseFormatter::formatResponse($result['meta_description'], true);
                        }
                    } else {
                        $result = OpenRouterResponseFormatter::formatResponse($result, true);
                    }
                    
                    // Update content in database
                    if ($this->updateContentSEO($item, $job->content_type, $result)) {
                        $job->success_count++;
                    } else {
                        $failed[] = [
                            'id' => $itemId,
                            'error' => 'Failed to update content'
                        ];
                        $job->failed_count++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error processing item in batch job', [
                        'item_id' => $itemId,
                        'content_type' => $job->content_type,
                        'error' => $e->getMessage()
                    ]);
                    
                    $failed[] = [
                        'id' => $itemId,
                        'error' => $e->getMessage()
                    ];
                    $job->failed_count++;
                }
                
                // Update job progress
                $job->processed_items = $index + 1;
                $job->failed_items = $failed;
                $job->save();
            }
            
            $job->status = 'completed';
            $job->save();
            
        } catch (\Exception $e) {
            Log::error('Error processing batch job', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage()
            ]);
            
            $job->status = 'failed';
            $job->error_message = $e->getMessage();
            $job->save();
        }
    }
    
    // Helper methods to get content, prepare prompts and update SEO content
    // These methods should match the logic in AIDashboardController
    protected function getContentObject($type, $id)
    {
        return match ($type) {
            'posts' => \App\Models\Post::with(['chapter.book.group.category'])->find($id),
            'chapters' => \App\Models\BookChapter::with(['book.group.category'])->find($id),
            'books' => \App\Models\Book::with(['group.category'])->find($id),
            'book_groups' => \App\Models\BookGroup::with(['category'])->find($id),
            default => null,
        };
    }
    
    protected function preparePrompt($promptTemplate, $content, $contentType)
    {
        // Same implementation as in AIDashboardController
        $replacements = [];

        switch ($contentType) {
            case 'posts':
                $replacements = [
                    '{{title}}' => $content->title ?? '',
                    '{{chapter_name}}' => $content->chapter->name ?? '',
                    '{{book_name}}' => $content->chapter->book->name ?? '',
                    '{{group_name}}' => $content->chapter->book->group->name ?? '',
                    '{{category_name}}' => $content->chapter->book->group->category->name ?? '',
                ];
                break;
                
            case 'chapters':
                $replacements = [
                    '{{name}}' => $content->name ?? '',
                    '{{book_name}}' => $content->book->name ?? '',
                    '{{group_name}}' => $content->book->group->name ?? '',
                    '{{category_name}}' => $content->book->group->category->name ?? '',
                ];
                break;

            case 'books':
                $replacements = [
                    '{{name}}' => $content->name ?? '',
                    '{{group_name}}' => $content->group->name ?? '',
                    '{{category_name}}' => $content->group->category->name ?? '',
                ];
                break;

            case 'book_groups':
                $replacements = [
                    '{{name}}' => $content->name ?? '',
                    '{{category_name}}' => $content->category->name ?? '',
                ];
                break;
        }

        // Replace all tokens
        return str_replace(array_keys($replacements), array_values($replacements), $promptTemplate);
    }
    
    protected function updateContentSEO($content, $contentType, $result)
    {
        try {
            switch ($contentType) {
                case 'posts':
                    \Illuminate\Support\Facades\DB::table('posts')->where('id', $content->id)->update([
                        'meta_title' => $result['meta_title'] ?? null,
                        'meta_description' => $result['meta_description'] ?? null
                    ]);
                    break;

                case 'chapters':
                case 'books':
                case 'book_groups':
                    $content->description = $result;
                    $content->save();
                    break;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating content SEO', [
                'content_type' => $contentType,
                'content_id' => $content->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
```

### 4. Add Controller Methods for Queue Management

Add these methods to `AIDashboardController.php`:

```php
/**
 * Queue bulk content generation instead of processing synchronously
 */
public function queueBulkGeneration(Request $request)
{
    try {
        $contentType = $request->input('content_type');
        $filterType = $request->input('filter_type');
        $filterId = $request->input('filter_id');
        $model = $request->input('model', 'grok-2');
        $promptText = $request->input('prompt');
        $promptId = $request->input('prompt_id');
        $useHtmlMeta = (bool)$request->input('use_html_meta', false);
        
        // Use prompt from database if ID provided
        if ($promptId) {
            $promptObj = Prompt::find($promptId);
            if ($promptObj) {
                $promptText = $promptObj->prompt_text;
            }
        }
        
        // Get content items based on filter
        $items = $this->getContentByFilter($contentType, $filterType, $filterId);
        
        if (count($items) === 0) {
            return response()->json([
                'success' => false,
                'error' => 'No content items found matching the filter criteria'
            ], 404);
        }
        
        // Extract item IDs
        $itemIds = $items->pluck('id')->toArray();
        
        // Prepare settings array
        $settings = [
            'model' => $model,
            'prompt' => $promptText,
            'max_tokens' => (int)$request->input('max_tokens', 4000),
            'temperature' => (float)$request->input('temperature', 0.7),
            'use_html_meta' => $useHtmlMeta,
        ];
        
        // Add model-specific parameters
        if (str_starts_with($model, 'deepseek')) {
            $systemMessage = $request->input('system_message');
            if (empty($systemMessage)) {
                $systemMessage = $this->getSystemMessage($contentType);
            }
            
            $settings['system_message'] = $systemMessage;
            $settings['model_variant'] = $request->input('deepseek_model', 'deepseek-chat');
        }
        
        // Create a new job record
        $job = new \App\Models\AIContentJob([
            'batch_id' => uniqid('batch_', true),
            'user_id' => Auth::id(),
            'content_type' => $contentType,
            'total_items' => count($itemIds),
            'status' => 'pending',
            'settings' => $settings,
            'item_ids' => $itemIds,
        ]);
        
        $job->save();
        
        // Dispatch background job
        \App\Jobs\ProcessAIContentBatch::dispatch($job->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Bulk generation job has been queued',
            'job_id' => $job->id,
            'batch_id' => $job->batch_id,
            'total_items' => count($itemIds)
        ]);
    } catch (\Exception $e) {
        Log::error('Error queueing bulk generation job', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Error processing request: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Check job status
 */
public function checkJobStatus(Request $request, $jobId)
{
    try {
        $job = \App\Models\AIContentJob::findOrFail($jobId);
        
        return response()->json([
            'success' => true,
            'id' => $job->id,
            'batch_id' => $job->batch_id,
            'status' => $job->status,
            'total_items' => $job->total_items,
            'processed_items' => $job->processed_items,
            'success_count' => $job->success_count,
            'failed_count' => $job->failed_count,
            'progress_percentage' => $job->progress_percentage,
            'created_at' => $job->created_at->format('Y-m-d H:i:s'),
            'settings' => $job->settings,
            'failed_items' => $job->failed_items,
            'content_type' => $job->content_type,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error checking job status: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * List jobs for current user
 */
public function listJobs(Request $request)
{
    try {
        $jobs = \App\Models\AIContentJob::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error listing jobs: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Jobs management view
 */
public function jobsView()
{
    $jobs = \App\Models\AIContentJob::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    return view('admin.ai-dashboard.jobs', compact('jobs'));
}
```

### 5. Add Routes for Queue Management

Add these routes in `web.php`:

```php
// Inside the AI Dashboard route group
Route::post('/queue-bulk-generation', [App\Http\Controllers\Admin\AIDashboardController::class, 'queueBulkGeneration'])->name('admin.ai-dashboard.queue-bulk-generation');
Route::get('/job-status/{jobId}', [App\Http\Controllers\Admin\AIDashboardController::class, 'checkJobStatus'])->name('admin.ai-dashboard.job-status');
Route::get('/jobs', [App\Http\Controllers\Admin\AIDashboardController::class, 'listJobs'])->name('admin.ai-dashboard.jobs');
Route::get('/jobs-view', [App\Http\Controllers\Admin\AIDashboardController::class, 'jobsView'])->name('admin.ai-dashboard.jobs-view');
```

### 6. Update JavaScript to Use Queue System

Modify the bulk generate functionality in `stats_main.js`:

```javascript
// Modify the existing bulk generate button handler
$("#bulk-generate-start-btn").on('click', function() {
    const selectedItems = $(".item-checkbox:checked");

    if (selectedItems.length === 0) {
        alert('Vui lòng chọn ít nhất một mục');
        return;
    }

    const model = $("#bulk-model").val();
    const prompt = $("#bulk-prompt").val();
    const temperature = $("#bulk-temperature").val();
    const maxTokens = $("#bulk-max-tokens").val();
    const systemMessage = $("#bulk-system-message").val();
    const useHtmlMeta = $("#bulk-use-html-meta").is(':checked');

    if (!prompt) {
        alert('Vui lòng cung cấp lời nhắc');
        return;
    }

    // Show progress
    $("#bulk-progress-container").removeClass('d-none');
    $("#bulk-progress-message").html('<div class="text-indigo-600">Đang xếp hàng công việc...</div>');
    $("#bulk-progress-bar").css('width', '0%').attr('aria-valuenow', 0);
    $("#bulk-progress-percentage").text('0%');
    $("#bulk-processed").text('0');
    $("#bulk-total").text(selectedItems.length);

    // Disable the button
    $(this).prop('disabled', true);
    $(this).text('Đang xử lý...');

    // Get all selected IDs
    const selectedIds = $.map(selectedItems, function(item) {
        return $(item).data('id');
    }).join(',');

    // Create form data
    const formData = new FormData();
    formData.append('content_type', currentContentType);
    formData.append('filter_type', 'ids');
    formData.append('filter_id', selectedIds);
    formData.append('model', model);
    formData.append('prompt', prompt);
    formData.append('temperature', temperature);
    formData.append('max_tokens', maxTokens);
    formData.append('use_html_meta', useHtmlMeta ? '1' : '0');

    if (model.startsWith('deepseek') && systemMessage) {
        formData.append('system_message', systemMessage);
    }

    // Use the new queue endpoint
    $.ajax({
        url: `${apiBaseUrl}/admin/ai-dashboard/queue-bulk-generation`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success) {
                // Store job ID in session storage for tracking
                sessionStorage.setItem('current_ai_job_id', data.job_id);
                
                // Show job created message
                $("#bulk-progress-message").html(`
                    <div class="alert alert-success">
                        <strong>Công việc đã được xếp hàng thành công!</strong><br>
                        ID: ${data.batch_id}<br>
                        Tổng số mục: ${data.total_items}<br>
                        <a href="${apiBaseUrl}/admin/ai-dashboard/jobs-view" class="alert-link">
                            Xem trạng thái công việc
                        </a>
                    </div>
                `);
                
                // Change button text
                $("#bulk-generate-start-btn").text('Đã xếp hàng thành công');
                
                // Close modal after a delay
                setTimeout(() => {
                    if (bulkGenerateModal) {
                        bulkGenerateModal.hide();
                    }
                }, 5000);
            } else {
                alert('Lỗi: ' + (data.error || 'Không thể xếp hàng các mục'));
                $("#bulk-generate-start-btn").prop('disabled', false);
                $("#bulk-generate-start-btn").text('Bắt Đầu Tạo');
            }
        },
        error: function(error) {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi. Vui lòng thử lại.');
            $("#bulk-generate-start-btn").prop('disabled', false);
            $("#bulk-generate-start-btn").text('Bắt Đầu Tạo');
        }
    });
});

// Add function to check job status periodically
function startJobStatusCheck(jobId) {
    return setInterval(() => {
        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/job-status/${jobId}`,
            type: 'GET',
            success: function(data) {
                if (data.success) {
                    // Update notification if visible
                    if ($("#job-notification").length > 0) {
                        const statusText = {
                            'pending': 'đang chờ',
                            'processing': 'đang xử lý',
                            'completed': 'hoàn thành',
                            'failed': 'thất bại'
                        }[data.status] || data.status;
                        
                        $("#job-status-text").text(statusText);
                        $("#job-progress-text").text(`${data.processed_items}/${data.total_items} (${data.progress_percentage}%)`);
                    }
                    
                    // If job is completed or failed, stop checking
                    if (data.status === 'completed' || data.status === 'failed') {
                        sessionStorage.removeItem('current_ai_job_id');
                        if (window.jobStatusInterval) {
                            clearInterval(window.jobStatusInterval);
                        }
                    }
                }
            },
            error: function(error) {
                console.error('Error checking job status:', error);
            }
        });
    }, 5000); // Check every 5 seconds
}

// Add notification when returning to page with active job
$(document).ready(function() {
    const activeJobId = sessionStorage.getItem('current_ai_job_id');
    if (activeJobId) {
        // Check job status once
        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/job-status/${activeJobId}`,
            type: 'GET',
            success: function(data) {
                if (data.success) {
                    // Show notification if job is still in progress
                    if (data.status === 'pending' || data.status === 'processing') {
                        const statusText = data.status === 'pending' ? 'đang chờ' : 'đang xử lý';
                        
                        // Add notification if not already there
                        if ($("#job-notification").length === 0) {
                            $('body').append(`
                                <div id="job-notification" class="fixed bottom-4 right-4 w-80 bg-white rounded-lg shadow-lg border-l-4 border-indigo-500 p-4 z-50">
                                    <div class="flex justify-between">
                                        <div>
                                            <h5 class="font-medium">Công việc <span id="job-status-text">${statusText}</span></h5>
                                            <p class="text-sm text-gray-600">ID: ${data.batch_id || activeJobId}</p>
                                            <p class="text-sm text-gray-600">Tiến độ: <span id="job-progress-text">${data.processed_items}/${data.total_items} (${data.progress_percentage}%)</span></p>
                                            <a href="${apiBaseUrl}/admin/ai-dashboard/jobs-view" class="text-sm text-indigo-600 hover:underline">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                        <button class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('job-notification').remove()">
                                            <span class="iconify" data-icon="mdi-close"></span>
                                        </button>
                                    </div>
                                </div>
                            `);
                        }
                        
                        // Start checking job status
                        window.jobStatusInterval = startJobStatusCheck(activeJobId);
                    } 
                    // Clean up if job is complete
                    else if (data.status === 'completed' || data.status === 'failed') {
                        sessionStorage.removeItem('current_ai_job_id');
                    }
                }
            }
        });
    }
});
```

### 7. Create Jobs List View

Create a new Blade template for the jobs list:

```blade
<!-- resources/views/admin/ai-dashboard/jobs.blade.php -->
@extends('admin_layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Công Việc AI</h2>
            <p class="mt-1 text-gray-600">Quản lý và theo dõi các công việc tạo nội dung AI</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-dashboard.stats') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-stroke py-2 px-4 text-center font-medium text-black hover:bg-gray-50 sm:px-6">
                <span class="iconify" data-icon="mdi-arrow-left"></span>
                Quay Lại Thống Kê
            </a>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-800">Công Việc Đã Xếp Hàng</h3>
        </div>

        @if($jobs->isEmpty())
            <div class="rounded-lg bg-gray-50 p-8 text-center">
                <div class="mb-4">
                    <span class="iconify text-4xl text-gray-400" data-icon="mdi-text-box-outline"></span>
                </div>
                <h4 class="mb-2 text-lg font-medium text-gray-700">Không có công việc nào</h4>
                <p class="text-gray-500">Bạn chưa tạo bất kỳ công việc tạo nội dung hàng loạt nào</p>
            </div>
        @else
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="py-4 px-4 font-medium text-gray-700">ID</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Loại Nội Dung</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Tiến Độ</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Trạng Thái</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Ngày Tạo</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <span class="text-xs font-medium">{{ $job->batch_id }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    @php
                                        $typeLabels = [
                                            'posts' => 'Bài Viết',
                                            'chapters' => 'Chương Sách',
                                            'books' => 'Sách',
                                            'book_groups' => 'Nhóm Sách'
                                        ];
                                    @endphp
                                    <span class="font-medium">{{ $typeLabels[$job->content_type] ?? $job->content_type }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <div class="flex items-center">
                                        <div class="mr-4 w-full max-w-36">
                                            <div class="h-2 w-full rounded-full bg-gray-200">
                                                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $job->progress_percentage }}%"></div>
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $job->processed_items }}/{{ $job->total_items }}</span>
                                    </div>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    @if($job->status == 'pending')
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            Đang chờ
                                        </span>
                                    @elseif($job->status == 'processing')
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Đang xử lý
                                        </span>
                                    @elseif($job->status == 'completed')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Hoàn thành
                                        </span>
                                    @elseif($job->status == 'failed')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                            Thất bại
                                        </span>
                                    @endif
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <span class="text-sm">{{ $job->created_at->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <button 
                                        type="button" 
                                        class="view-job-details inline-flex items-center text-indigo-600 hover:text-indigo-800"
                                        data-job-id="{{ $job->id }}"
                                    >
                                        <span class="iconify mr-1" data-icon="mdi-eye"></span>
                                        Chi tiết
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
    
    <!-- Job Details Modal -->
    <div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobDetailsModalLabel">Chi Tiết Công Việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div id="job-details-content">
                        <div class="text-center">
                            <div class="spinner-border text-indigo-600" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-2">Đang tải thông tin...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Job details modal functionality
        const jobDetailsModal = new bootstrap.Modal(document.getElementById('jobDetailsModal'));
        
        // Event handlers for job details modal
        document.querySelectorAll('.view-job-details').forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.getAttribute('data-job-id');
                
                // Show modal
                jobDetailsModal.show();
                
                // Show loading state
                document.getElementById('job-details-content').innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-indigo-600" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2">Đang tải thông tin...</p>
                    </div>
                `;
                
                // Load job details
                fetch(`/admin/ai-dashboard/job-status/${jobId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Format status text
                            const statusText = {
                                'pending': 'Đang chờ',
                                'processing': 'Đang xử lý',
                                'completed': 'Hoàn thành',
                                'failed': 'Thất bại'
                            }[data.status] || data.status;
                            
                            // Format content type
                            const contentTypeText = {
                                'posts': 'Bài Viết',
                                'chapters': 'Chương Sách',
                                'books': 'Sách',
                                'book_groups': 'Nhóm Sách'
                            }[data.content_type] || data.content_type;
                            
                            // Set job details
                            let failedItemsHtml = '';
                            if (data.failed_items && data.failed_items.length > 0) {
                                failedItemsHtml = `
                                    <div class="mt-4">
                                        <h6 class="font-medium">Các mục thất bại (${data.failed_items.length})</h6>
                                        <div class="mt-2 max-h-40 overflow-y-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="bg-gray-50">
                                                        <th class="py-2 px-3 text-left">ID</th>
                                                        <th class="py-2 px-3 text-left">Lỗi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${data.failed_items.map(item => `
                                                        <tr class="border-t border-gray-200">
                                                            <td class="py-2 px-3">${item.id}</td>
                                                            <td class="py-2 px-3">${item.error}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            }
                            
                            document.getElementById('job-details-content').innerHTML = `
                                <div class="space-y-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h5 class="font-medium text-xl">${contentTypeText}</h5>
                                            <p class="text-sm text-gray-600">ID: ${data.batch_id}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                            data.status === 'completed' ? 'bg-green-100 text-green-800' :
                                            data.status === 'failed' ? 'bg-red-100 text-red-800' :
                                            data.status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-blue-100 text-blue-800'
                                        }">
                                            ${statusText}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Tiến độ</p>
                                            <div class="mt-1 flex items-center">
                                                <div class="mr-3 w-full">
                                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                                        <div class="h-full rounded-full bg-indigo-600" style="width: ${data.progress_percentage}%"></div>
                                                    </div>
                                                </div>
                                                <span class="text-sm">${data.progress_percentage}%</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Tổng Kết</p>
                                            <p class="text-sm mt-1">
                                                <span class="text-green-600">${data.success_count}</span> thành công,
                                                <span class="text-red-600">${data.failed_count}</span> thất bại
                                                (${data.processed_items}/${data.total_items})
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Cài Đặt</p>
                                        <div class="mt-1 rounded bg-gray-50 p-3 text-sm">
                                            <p><span class="font-medium">Mô Hình:</span> ${data.settings?.model || 'N/A'}</p>
                                            <p><span class="font-medium">Nhiệt Độ:</span> ${data.settings?.temperature || 'N/A'}</p>
                                            <p><span class="font-medium">Token Tối Đa:</span> ${data.settings?.max_tokens || 'N/A'}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Prompt</p>
                                        <div class="mt-1 rounded bg-gray-50 p-3 text-sm">
                                            <pre class="whitespace-pre-wrap">${data.settings?.prompt || 'N/A'}</pre>
                                        </div>
                                    </div>
                                    
                                    ${failedItemsHtml}
                                </div>
                            `;
                        } else {
                            document.getElementById('job-details-content').innerHTML = `
                                <div class="text-center text-red-500">
                                    <p>Lỗi khi tải thông tin: ${data.error || 'Lỗi không xác định'}</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        document.getElementById('job-details-content').innerHTML = `
                            <div class="text-center text-red-500">
                                <p>Lỗi khi tải thông tin: ${error.message || 'Lỗi kết nối'}</p>
                            </div>
                        `;
                    });
            });
        });
    });
</script>
@endsection
```

### 8. Configure Laravel Queue

1. Update `.env` file:
```
QUEUE_CONNECTION=database
```

2. Create queue tables:
```bash
php artisan queue:table
php artisan migrate
```

3. Start a queue worker (development):
```bash
php artisan queue:work --tries=3 --timeout=3600
```

4. For production, use Supervisor:
Create a config file at `/etc/supervisor/conf.d/laravel-worker.conf`:

```
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --tries=3 --timeout=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 9. Testing Plan

1. Verify database migrations run successfully
   - Run `php artisan migrate` to create the new table
   - Check that the table structure is correct

2. Test creating a background job with a small batch (2-3 items)
   - Select 2-3 content items from the dashboard
   - Start a bulk generation job
   - Verify the job is created and queued

3. Confirm the queue worker processes the job
   - Check that the queue worker picks up the job
   - Monitor the job progress through the UI or database

4. Check job status updates correctly
   - Verify the job status changes from "pending" to "processing" to "completed"
   - Check that the progress percentage and counts update

5. Test notifications when returning to the page
   - Start a job and navigate away
   - Return to the dashboard
   - Check that the notification appears with the correct status

6. Verify HTML formatting from OpenRouter responses is handled correctly
   - Generate content that includes HTML
   - Verify that HTML entities are properly decoded
   - Check that content is saved correctly in the database

7. Test with larger batches (50+ items)
   - Select 50+ content items
   - Start a bulk generation job
   - Verify the job processes all items correctly

8. Verify the error handling and retry mechanisms
   - Intentionally cause errors (e.g., invalid prompt)
   - Check that failed items are tracked
   - Verify error messages are logged and displayed

### 10. Benefits of This Implementation

1. **Improved UX**: Users can start a job and navigate away
2. **Reliable Processing**: Long-running jobs won't time out
3. **Error Resilience**: Failed items are tracked separately
4. **Progress Tracking**: Users can monitor job progress
5. **Resource Efficiency**: The system can handle more concurrent users
6. **Rate Limiting Control**: Background processing allows better API throttling

This implementation provides a robust solution for processing large batches of AI content generation requests without overwhelming the system or causing timeout errors.