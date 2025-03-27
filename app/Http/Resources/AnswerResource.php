<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Check if we received an array instead of a model
        $isArray = is_array($this->resource);
        $data = $isArray ? $this->resource : null;
        
        try {
            return [
                'id' => $isArray ? $data['id'] : $this->id,
                'content' => $isArray ? $data['content'] : $this->content,
                'is_ai' => $isArray ? $data['is_ai'] : $this->is_ai,
                'created_at' => $isArray ? $data['created_at'] : $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $isArray ? $data['updated_at'] : $this->updated_at->format('Y-m-d H:i:s'),

                // User information (null for AI answers)
                'user' => $this->when(($isArray ? isset($data['user']) : $this->user), function() use ($isArray, $data) {
                    $user = $isArray ? $data['user'] : $this->user;
                    return [
                        'id' => $isArray ? $user['id'] : $user->id,
                        'name' => $isArray ? $user['name'] : $user->name,
                        'avatar_url' => null,
                    ];
                }),

                // Question reference
                'question_id' => $isArray ? $data['question_id'] : $this->question_id,

                // Optional: Content preview
                'content_preview' => $this->when(
                    true,
                    function() use ($isArray, $data) {
                        $content = $isArray ? $data['content'] : $this->content;
                        return substr(strip_tags($content), 0, 150) . (strlen(strip_tags($content)) > 150 ? '...' : '');
                    }
                ),
            ];
        } catch (\Exception $e) {
            Log::error('Error in AnswerResource: ' . $e->getMessage(), [
                'resource' => $this->resource,
                'is_array' => $isArray
            ]);
            
            // Provide a minimal response if we encounter an error
            return [
                'id' => $isArray ? ($data['id'] ?? 0) : ($this->id ?? 0),
                'content' => 'Error loading answer content',
                'is_ai' => false,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
                'user' => null,
                'question_id' => $isArray ? ($data['question_id'] ?? 0) : ($this->question_id ?? 0),
            ];
        }
    }
}
