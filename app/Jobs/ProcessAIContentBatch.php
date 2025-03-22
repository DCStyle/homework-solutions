<?php

namespace App\Jobs;

use App\Models\AIContentJob;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
                    
                    // Generate content - Note: we no longer pass use_html_meta since we're moving away from that approach
                    $result = $aiService->generate(
                        $job->settings['model'],
                        $prompt,
                        $options
                    );
                    
                    // Format the result with our improved formatter
                    $formattedResult = \App\Helpers\OpenRouterResponseFormatter::formatResponse($result, true);
                    
                    // Update content in database
                    if ($this->updateContentSEO($item, $job->content_type, $formattedResult)) {
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
    
    /**
     * Update content SEO data with the formatted result
     * Modified to handle the updated approach where we don't use separate meta_title and meta_description
     */
    protected function updateContentSEO($content, $contentType, $result)
    {
        try {
            switch ($contentType) {
                case 'posts':
                    // For posts, we now just update the description field and leave meta_title as null
                    \Illuminate\Support\Facades\DB::table('posts')->where('id', $content->id)->update([
                        'meta_description' => $result
                    ]);
                    break;

                case 'chapters':
                case 'books':
                case 'book_groups':
                    // For other content types, the behavior remains the same
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