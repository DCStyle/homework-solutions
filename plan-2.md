# OpenRouter API Error Handling Fixes

## Problem Analysis

After analyzing the error logs, I've identified an interesting scenario:

1. The OpenRouter API returns a 400 error (Bad Request) with message `[object Object]`.
2. Despite the error status code, there is valid content in the response.
3. The system currently fails to process this data because it treats the HTTP error as a complete failure.

Looking at the successful part of the response, it contains:
```
"message": {
  "role": "assistant",
  "content": "Meta Title: Bài 2: Cà cá trang 8 SGK Tiếng Việt lớp 1 Cánh diều\nMeta Description: Khám phá bài học cà cá trang 8 SGK Tiếng Việt lớp 1 tập 1 Cánh diều. Hướng dẫn chi tiết giúp học sinh nắm vững kiến thức và kỹ năng môn Tiếng Việt."
}
```

## Solution Implementation

### 1. Update ProcessAIContentBatch.php

Enhance the job class to handle error responses containing valid content:

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
use GuzzleHttp\Exception\ClientException;

class ProcessAIContentBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    protected $jobId;
    public $tries = 3; // Allow retries at the job level

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
                    // Dynamic delay based on batch size
                    $delayMs = min(1000, 200 + ($job->total_items > 50 ? 800 : 300));
                    usleep($delayMs * 1000); // Convert to microseconds
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
                    
                    try {
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
                        
                    } catch (ClientException $e) {
                        // Special handling for HTTP client exceptions (4xx status codes)
                        $response = $e->getResponse();
                        $statusCode = $response->getStatusCode();
                        $responseBody = $response->getBody()->getContents();
                        
                        Log::warning('ClientException in batch job', [
                            'item_id' => $itemId,
                            'status_code' => $statusCode,
                            'response_preview' => substr($responseBody, 0, 200)
                        ]);
                        
                        // Try to extract valid content from error response
                        $result = $this->extractResultFromErrorResponse($responseBody, $job->content_type);
                        
                        if (!$result) {
                            throw $e; // Re-throw if we couldn't extract anything useful
                        }
                        
                        Log::info('Successfully extracted content from error response', [
                            'item_id' => $itemId,
                            'content_type' => $job->content_type
                        ]);
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
                    
                    // Add delay after error to avoid rate limiting
                    sleep(2);
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $job->status = 'failed';
            $job->error_message = $e->getMessage();
            $job->save();
        }
    }
    
    /**
     * Attempt to extract valid content from error responses
     */
    protected function extractResultFromErrorResponse($responseBody, $contentType)
    {
        try {
            $data = json_decode($responseBody, true);
            
            // Check for common patterns in the error response
            
            // Check if there's a response field that might be a stringified JSON
            if (isset($data['response']) && is_string($data['response'])) {
                $responseData = json_decode($data['response'], true);
                
                // Check for OpenRouter/OpenAI format inside the response field
                if ($responseData && isset($responseData['choices']) && 
                    isset($responseData['choices'][0]['message']['content'])) {
                    
                    $content = $responseData['choices'][0]['message']['content'];
                    
                    // For posts, check if we need to parse further
                    if ($contentType === 'posts' && 
                        (stripos($content, 'meta title') !== false || 
                         stripos($content, 'tiêu đề meta') !== false)) {
                        
                        return $this->extractTitleDescription($content);
                    }
                    
                    return $content;
                }
            }
            
            // Check for direct nested content
            if (isset($data['choices']) && isset($data['choices'][0]['message']['content'])) {
                $content = $data['choices'][0]['message']['content'];
                
                if ($contentType === 'posts' && 
                    (stripos($content, 'meta title') !== false || 
                     stripos($content, 'tiêu đề meta') !== false)) {
                    
                    return $this->extractTitleDescription($content);
                }
                
                return $content;
            }
            
            // If we couldn't find structured content, try regex on the whole response
            if (preg_match('/Meta Title: (.*?)(\n|$).*?Meta Description: (.*?)(\n|$)/s', $responseBody, $matches)) {
                return [
                    'meta_title' => trim($matches[1]),
                    'meta_description' => trim($matches[3])
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error parsing error response', [
                'error' => $e->getMessage(),
                'response_preview' => substr($responseBody, 0, 300)
            ]);
            return null;
        }
    }
    
    /**
     * Extract title and description from formatted text
     */
    protected function extractTitleDescription($content)
    {
        $result = [
            'meta_title' => '',
            'meta_description' => ''
        ];
        
        // Match Meta Title
        if (preg_match('/(meta title|tiêu đề meta)\s*:\s*(.*?)(?:\n|$)/i', $content, $matches)) {
            $result['meta_title'] = trim($matches[2]);
        }
        
        // Match Meta Description
        if (preg_match('/(meta description|mô tả meta)\s*:\s*(.*?)(?:$|(?=\n\n))/is', $content, $matches)) {
            $result['meta_description'] = trim($matches[2]);
        }
        
        return $result;
    }
    
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

### 2. Enhance OpenRouterResponseFormatter.php

```php
<?php

namespace App\Helpers;

class OpenRouterResponseFormatter
{
    /**
     * Format and decode OpenRouter API response for display
     *
     * @param mixed $response The response from OpenRouter
     * @param bool $parseMarkdown Whether to parse markdown formatting
     * @return string The formatted and decoded content
     */
    public static function formatResponse($response, $parseMarkdown = true)
    {
        // If response is a string (raw JSON), decode it
        if (is_string($response) && self::isJson($response)) {
            $response = json_decode($response, true);
        }

        // Extract content from different possible response formats
        $content = '';

        if (is_array($response) && isset($response['message']['content'])) {
            // Standard JSON array format
            $content = $response['message']['content'];
        } elseif (is_array($response) && isset($response['meta_title'])) {
            // Already have meta title, likely from error response extraction
            return $response;
        } elseif (is_object($response)) {
            if (isset($response->message) && isset($response->message->content)) {
                // Object format with nested message
                $content = $response->message->content;
            } elseif (isset($response->choices) && !empty($response->choices)) {
                // OpenAI-like format
                if (isset($response->choices[0]->message->content)) {
                    $content = $response->choices[0]->message->content;
                } elseif (isset($response->choices[0]->text)) {
                    $content = $response->choices[0]->text;
                } else {
                    // Try to parse the whole choice object as JSON
                    $content = json_encode($response->choices[0]);
                }
            } elseif (isset($response->content)) {
                // Simple object with content property
                $content = $response->content;
            } else {
                // Try to extract from the entire response
                $content = json_encode($response);
            }
        } elseif (is_string($response)) {
            // Already a string, use as is
            $content = $response;
            
            // Check if it might be in Meta Title/Description format
            if (preg_match('/(meta title|tiêu đề meta)/i', $content) && 
                preg_match('/(meta description|mô tả meta)/i', $content)) {
                
                $result = [
                    'meta_title' => '',
                    'meta_description' => ''
                ];
                
                // Extract title
                if (preg_match('/(meta title|tiêu đề meta)\s*:\s*(.*?)(?:\n|$)/i', $content, $matches)) {
                    $result['meta_title'] = trim($matches[2]);
                }
                
                // Extract description
                if (preg_match('/(meta description|mô tả meta)\s*:\s*(.*?)(?:$|(?=\n\n))/is', $content, $matches)) {
                    $result['meta_description'] = trim($matches[2]);
                }
                
                if (!empty($result['meta_title']) || !empty($result['meta_description'])) {
                    // Return structured data if we found meta tags
                    return $result;
                }
            }
        }

        // If no content was found, return empty string or error message
        if (empty($content)) {
            return "No content found in response.";
        }

        // Decode Unicode escape sequences
        $decodedContent = self::decodeUnicodeEscapes($content);

        // Optionally parse markdown
        if ($parseMarkdown) {
            $decodedContent = self::parseMarkdown($decodedContent);
        }

        return $decodedContent;
    }

    /**
     * Decode Unicode escape sequences in a string
     */
    private static function decodeUnicodeEscapes($input)
    {
        // Method 1: Using json_decode (handles \uXXXX sequences)
        $decoded = null;
        
        // If input is not a string, try to convert
        if (!is_string($input)) {
            $input = json_encode($input);
        }
        
        try {
            // Add quotes if not present
            if ($input[0] !== '"' && $input[0] !== "'") {
                $decoded = json_decode('"' . str_replace('"', '\"', $input) . '"');
            } else {
                $decoded = json_decode($input);
            }
        } catch (\Exception $e) {
            // Ignore errors, will try alternative method
        }

        // If json_decode failed, try alternative method
        if ($decoded === null) {
            // Method 2: Using preg_replace_callback for \uXXXX sequences
            $decoded = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            }, $input);
        }

        // Special handling for Vietnamese characters
        if (is_string($decoded)) {
            // Handle escaped characters
            $decoded = preg_replace_callback('/\\\\([\x{00C0}-\x{1EF9}])/u', function ($matches) {
                return $matches[1];
            }, $decoded);
        }

        return $decoded ?: $input; // Return original if both methods fail
    }

    /**
     * Basic markdown parsing to HTML
     */
    private static function parseMarkdown($text)
    {
        // Bold: Convert **text** to <strong>text</strong>
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);

        // Italic: Convert *text* or _text_ to <em>text</em>
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/_(.*?)_/s', '<em>$1</em>', $text);

        // Paragraphs: Convert double newlines to paragraphs
        $paragraphs = preg_split('/\n\n+/', $text);
        $paragraphs = array_map(function($p) {
            return '<p>' . str_replace("\n", '<br>', $p) . '</p>';
        }, $paragraphs);

        $text = implode('', $paragraphs);

        return $text;
    }

    /**
     * Check if a string is valid JSON
     */
    private static function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
```

### 3. Add AIContentJob Model Enhancement

Add the ability to retry failed jobs by adding this method to the `AIContentJob` model:

```php
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
```

### 4. Add Route and Controller Method for Retry

Add this route to `web.php`:

```php
Route::post('/jobs/{jobId}/retry', [App\Http\Controllers\Admin\AIDashboardController::class, 'retryFailedItems'])->name('admin.ai-dashboard.retry-job');
```

Add this method to `AIDashboardController.php`:

```php
/**
 * Retry failed items in a job
 */
public function retryFailedItems(Request $request, $jobId)
{
    try {
        $job = \App\Models\AIContentJob::findOrFail($jobId);
        
        // Check if job belongs to current user
        if ($job->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thử lại công việc này');
        }
        
        // Retry failed items
        $newJobId = $job->retryFailedItems();
        
        if ($newJobId) {
            return redirect()->route('admin.ai-dashboard.jobs-view')
                ->with('success', "Đã tạo công việc mới #{$newJobId} để thử lại các mục lỗi");
        } else {
            return redirect()->back()->with('error', 'Không có mục lỗi nào để thử lại');
        }
    } catch (\Exception $e) {
        Log::error('Error retrying failed items', [
            'job_id' => $jobId,
            'error' => $e->getMessage()
        ]);
        
        return redirect()->back()->with('error', 'Lỗi khi thử lại: ' . $e->getMessage());
    }
}
```

### 5. Add Retry Button to Jobs View

Update the `jobs.blade.php` template to add a retry button:

```html
<!-- Add in the actions column of your jobs table -->
@if($job->status === 'completed' && $job->failed_count > 0)
    <form action="{{ route('admin.ai-dashboard.retry-job', $job->id) }}" method="POST" class="d-inline">
        @csrf
        <button 
            type="submit" 
            class="retry-failed-items inline-flex items-center text-orange-600 hover:text-orange-800 ml-3"
        >
            <span class="iconify mr-1" data-icon="mdi-refresh"></span>
            Thử lại
        </button>
    </form>
@endif
```

## Implementation Steps

1. Create a backup of the affected files before making changes
2. Add the new code to handle error responses in `ProcessAIContentBatch.php`
3. Update the `OpenRouterResponseFormatter` class to better handle different response formats
4. Add the retry functionality to the `AIContentJob` model
5. Add the retry controller method and route
6. Update the jobs view to include the retry button

## Testing Plan

1. Process a small batch job (2-3 items) to verify the changes
2. Check the logs for any warning or error messages
3. Verify that items are still processed even when the API returns 400 errors
4. Test the retry functionality for failed items
5. Ensure Vietnamese characters are properly handled

These changes will make your bulk generation system more resilient against API errors while still extracting valid content whenever possible.