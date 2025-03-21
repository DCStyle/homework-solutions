<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\AIGenerationHistory;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BulkGenerationCompleted;

class BulkGenerateSEO implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contentType;
    protected $filterType;
    protected $filterId;
    protected $model;
    protected $prompt;
    protected $temperature;
    protected $maxTokens;
    protected $systemMessage;
    protected $useHtmlMeta;
    protected $userId;
    protected $historyId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        $contentType,
        $filterType,
        $filterId,
        $model,
        $prompt,
        $temperature,
        $maxTokens,
        $useHtmlMeta,
        $userId,
        $systemMessage = null,
        $historyId = null
    ) {
        $this->contentType = $contentType;
        $this->filterType = $filterType;
        $this->filterId = $filterId;
        $this->model = $model;
        $this->prompt = $prompt;
        $this->temperature = $temperature;
        $this->maxTokens = $maxTokens;
        $this->systemMessage = $systemMessage;
        $this->useHtmlMeta = $useHtmlMeta;
        $this->userId = $userId;
        $this->historyId = $historyId;
    }

    /**
     * Execute the job.
     */
    public function handle(AIService $aiService): void
    {
        try {
            $user = User::find($this->userId);
            if (!$user) {
                Log::error('User not found for BulkGenerateSEO job', ['user_id' => $this->userId]);
                return;
            }

            // Create or retrieve history record
            $history = $this->createOrGetHistory();

            // Get content items based on filter
            $items = $this->getContentByFilter();

            if (count($items) === 0) {
                Log::error('No content items found matching the filter criteria', [
                    'content_type' => $this->contentType,
                    'filter_type' => $this->filterType,
                    'filter_id' => $this->filterId
                ]);
                
                // Update history status
                $history->update([
                    'status' => 'failed',
                    'error_messages' => 'No content items found matching the filter criteria'
                ]);
                
                // Notify user that no items were found
                Notification::send($user, new BulkGenerationCompleted(0, 0, count($items), 'No content items found matching the filter criteria', $history->id));
                return;
            }

            // Update total items count
            $history->update([
                'total_items' => count($items)
            ]);

            $processed = 0;
            $failed = 0;
            $errors = [];
            $processedItems = [];

            // Process each item
            foreach ($items as $item) {
                try {
                    $prompt = $this->preparePrompt($item);

                    // Prepare options based on model
                    $options = [
                        'content_type' => $this->contentType,
                        'max_tokens' => (int)$this->maxTokens,
                        'temperature' => (float)$this->temperature,
                    ];

                    // Add system message for DeepSeek
                    if (strpos($this->model, 'deepseek') === 0 && $this->systemMessage) {
                        $options['system_message'] = $this->systemMessage;
                        $options['model_variant'] = 'deepseek-chat';
                    }

                    try {
                        $result = $aiService->generate($this->model, $prompt, $options, $this->useHtmlMeta);

                        // Format the result with our helper
                        if ($this->contentType === 'posts' && is_array($result)) {
                            // For posts with meta title and description
                            if (isset($result['meta_title'])) {
                                $result['meta_title'] = \App\Helpers\OpenRouterResponseFormatter::formatResponse($result['meta_title'], false);
                            }
                            if (isset($result['meta_description'])) {
                                $result['meta_description'] = \App\Helpers\OpenRouterResponseFormatter::formatResponse($result['meta_description'], true);
                            }
                        } else {
                            // For other content types
                            $result = \App\Helpers\OpenRouterResponseFormatter::formatResponse($result, true);
                        }

                        if ($result && $this->updateContentSEO($item, $result)) {
                            $processed++;
                            $processedItems[] = [
                                'id' => $item->id,
                                'name' => $item->name ?? $item->title ?? ('Item ' . $item->id),
                                'status' => 'success'
                            ];

                            // Clear cache for this item
                            $this->clearItemCache($item);
                        } else {
                            $failed++;
                            $errorMsg = "Failed to update item ID: {$item->id}";
                            $errors[] = $errorMsg;
                            $processedItems[] = [
                                'id' => $item->id,
                                'name' => $item->name ?? $item->title ?? ('Item ' . $item->id),
                                'status' => 'failed',
                                'error' => $errorMsg
                            ];
                        }
                    } catch (\TypeError $e) {
                        // Handle the OpenRouter ResponseData null ID error
                        if (strpos($e->getMessage(), 'Argument #1 ($id) must be of type string, null given') !== false) {
                            Log::error('OpenRouter returned null ID in response', [
                                'item_id' => $item->id,
                                'content_type' => $this->contentType,
                                'error' => $e->getMessage()
                            ]);
                            
                            // Try to generate a fallback result
                            $result = $this->generateFallbackContent($item);
                            
                            if ($result && $this->updateContentSEO($item, $result)) {
                                $processed++;
                                $processedItems[] = [
                                    'id' => $item->id,
                                    'name' => $item->name ?? $item->title ?? ('Item ' . $item->id),
                                    'status' => 'success',
                                    'note' => 'Generated using fallback method'
                                ];
                                $this->clearItemCache($item);
                            } else {
                                $failed++;
                                $errorMsg = "OpenRouter null ID error for item {$item->id}, fallback also failed";
                                $errors[] = $errorMsg;
                                $processedItems[] = [
                                    'id' => $item->id,
                                    'name' => $item->name ?? $item->title ?? ('Item ' . $item->id),
                                    'status' => 'failed',
                                    'error' => $errorMsg
                                ];
                            }
                        } else {
                            // Rethrow other type errors
                            throw $e;
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errorMsg = "Error processing item ID {$item->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    $processedItems[] = [
                        'id' => $item->id,
                        'name' => $item->name ?? $item->title ?? ('Item ' . $item->id),
                        'status' => 'failed',
                        'error' => $errorMsg
                    ];
                    Log::error('Error processing item in bulk generation', [
                        'item_id' => $item->id,
                        'content_type' => $this->contentType,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Update history with current progress
                $history->update([
                    'successful_items' => $processed,
                    'failed_items' => $failed,
                    'processed_items' => $processedItems
                ]);
            }

            // Update history status to completed
            $history->update([
                'status' => 'completed',
                'error_messages' => !empty($errors) ? implode("\n", $errors) : null
            ]);

            // Clear dashboard stats cache
            $this->clearDashboardCache();

            // Send notification to user
            Notification::send($user, new BulkGenerationCompleted($processed, $failed, count($items), implode("\n", $errors), $history->id));

        } catch (\Exception $e) {
            Log::error('Error in BulkGenerateSEO job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update history status to failed
            if ($this->historyId) {
                AIGenerationHistory::find($this->historyId)?->update([
                    'status' => 'failed',
                    'error_messages' => 'Error processing request: ' . $e->getMessage()
                ]);
            }
            
            // Notify user of the error
            $user = User::find($this->userId);
            if ($user) {
                $historyId = $this->historyId ?? ($this->createOrGetHistory()->id ?? null);
                Notification::send($user, new BulkGenerationCompleted(0, 0, 0, 'Error processing request: ' . $e->getMessage(), $historyId));
            }
        }
    }

    /**
     * Create or get the history record for this job
     */
    private function createOrGetHistory()
    {
        if ($this->historyId) {
            $history = AIGenerationHistory::find($this->historyId);
            if ($history) {
                return $history;
            }
        }
        
        // Create a new history record
        return AIGenerationHistory::create([
            'user_id' => $this->userId,
            'content_type' => $this->contentType,
            'filter_type' => $this->filterType,
            'filter_id' => $this->filterId,
            'prompt_text' => $this->prompt,
            'model' => $this->model,
            'settings' => [
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'use_html_meta' => $this->useHtmlMeta,
                'system_message' => $this->systemMessage
            ],
            'status' => 'processing'
        ]);
    }

    /**
     * Get content items based on filter
     */
    private function getContentByFilter()
    {
        $controller = app()->make(\App\Http\Controllers\Admin\AIDashboardController::class);
        
        // Use reflection to call the private method
        $method = new \ReflectionMethod($controller, 'getContentByFilter');
        $method->setAccessible(true);
        
        return $method->invoke($controller, $this->contentType, $this->filterType, $this->filterId);
    }

    /**
     * Prepare prompt for the specified content
     */
    private function preparePrompt($content)
    {
        $controller = app()->make(\App\Http\Controllers\Admin\AIDashboardController::class);
        
        // Use reflection to call the private method
        $method = new \ReflectionMethod($controller, 'preparePrompt');
        $method->setAccessible(true);
        
        return $method->invoke($controller, $this->prompt, $content, $this->contentType);
    }

    /**
     * Update the SEO data for content
     */
    private function updateContentSEO($content, $result)
    {
        $controller = app()->make(\App\Http\Controllers\Admin\AIDashboardController::class);
        
        // Use reflection to call the private method
        $method = new \ReflectionMethod($controller, 'updateContentSEO');
        $method->setAccessible(true);
        
        return $method->invoke($controller, $content, $this->contentType, $result);
    }

    /**
     * Clear cache for a specific item
     */
    private function clearItemCache($item)
    {
        $controller = app()->make(\App\Http\Controllers\Admin\AIDashboardController::class);
        
        // Use reflection to call the private method
        $method = new \ReflectionMethod($controller, 'clearItemCache');
        $method->setAccessible(true);
        
        return $method->invoke($controller, $item, $this->contentType);
    }

    /**
     * Clear dashboard cache
     */
    private function clearDashboardCache()
    {
        $controller = app()->make(\App\Http\Controllers\Admin\AIDashboardController::class);
        
        // Use reflection to call the private method
        $method = new \ReflectionMethod($controller, 'clearDashboardCache');
        $method->setAccessible(true);
        
        return $method->invoke($controller);
    }

    /**
     * Generate fallback content when the AI service fails
     * This creates basic SEO metadata based on the item's own content
     */
    private function generateFallbackContent($item)
    {
        try {
            $title = '';
            $description = '';
            
            // Extract title and basic info based on content type
            switch ($this->contentType) {
                case 'posts':
                    $title = $item->title ?? '';
                    $content = $item->content ?? '';
                    $description = $this->generateDescriptionFromContent($content, 160);
                    
                    return [
                        'meta_title' => $this->generateMetaTitle($title),
                        'meta_description' => $description
                    ];
                
                case 'books':
                    $title = $item->name ?? '';
                    $description = $item->description ?? '';
                    
                    if (empty($description)) {
                        $description = "Book: {$title}. Complete guide and resources.";
                    }
                    
                    return $this->generateDescriptionFromContent($description, 160);
                
                case 'chapters':
                    $title = $item->name ?? '';
                    $bookName = $item->book->name ?? '';
                    
                    return "Chapter: {$title} from {$bookName}. Learn comprehensive information and resources.";
                
                case 'book_groups':
                    $title = $item->name ?? '';
                    $description = $item->description ?? '';
                    
                    if (empty($description)) {
                        $description = "Book collection: {$title}. Complete guide and resources.";
                    }
                    
                    return $this->generateDescriptionFromContent($description, 160);
                
                default:
                    // Try to get a name or title property
                    if (isset($item->title)) {
                        $title = $item->title;
                    } elseif (isset($item->name)) {
                        $title = $item->name;
                    }
                    
                    if (isset($item->description)) {
                        $description = $item->description;
                    } elseif (isset($item->content)) {
                        $description = $this->generateDescriptionFromContent($item->content, 160);
                    }
                    
                    if (!empty($title)) {
                        return "Complete guide to {$title}. " . substr($description, 0, 120);
                    }
                    
                    return "Comprehensive information and resources.";
            }
        } catch (\Exception $e) {
            Log::error('Error generating fallback content', [
                'error' => $e->getMessage(),
                'item_id' => $item->id ?? null,
                'content_type' => $this->contentType
            ]);
            
            // Very basic fallback if everything else fails
            return "Comprehensive guide and information resources.";
        }
    }
    
    /**
     * Generate a meta title from the content title
     */
    private function generateMetaTitle($title)
    {
        if (empty($title)) {
            return "Comprehensive Guide";
        }
        
        // Remove any special characters
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', '', $title);
        
        // Limit to 60 characters for meta title
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        
        return $title;
    }
    
    /**
     * Generate a description from content
     */
    private function generateDescriptionFromContent($content, $length = 160)
    {
        if (empty($content)) {
            return "Comprehensive information and resources.";
        }
        
        // Strip HTML tags
        $content = strip_tags($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Limit length
        if (strlen($content) > $length) {
            $content = substr($content, 0, $length - 3) . '...';
        }
        
        return $content;
    }
} 