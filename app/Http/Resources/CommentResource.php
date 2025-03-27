<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
        
        return [
            'id' => $isArray ? $this->resource['id'] : $this->id,
            'content' => $isArray ? $this->resource['content'] : $this->content,
            'created_at' => $isArray ? $this->resource['created_at'] : $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $isArray ? $this->resource['updated_at'] : $this->updated_at->format('Y-m-d H:i:s'),

            // User information
            'user' => $this->when(($isArray ? isset($this->resource['user']) : $this->user), function() use ($isArray) {
                $user = $isArray ? $this->resource['user'] : $this->user;
                return [
                    'id' => $isArray ? $user['id'] : $user->id,
                    'name' => $isArray ? $user['name'] : $user->name,
                    'avatar_url' => null,
                    'role' => $isArray ? ($user['is_admin'] ?? false ? 'admin' : null) : ($user->isAdmin() ? 'admin' : null),
                ];
            }),

            // Parent and question references
            'parent_id' => $isArray ? $this->resource['parent_id'] : $this->parent_id,
            'question_id' => $isArray ? $this->resource['question_id'] : $this->question_id,

            // Nested replies (with conditional loading)
            'replies' => $isArray 
                ? (isset($this->resource['replies']) 
                    ? CommentResource::collection(collect($this->resource['replies'])) 
                    : [])
                : CommentResource::collection($this->whenLoaded('replies')),

            // Include replies count if available
            'replies_count' => $isArray 
                ? ($this->resource['replies_count'] ?? 0) 
                : ($this->replies_count ?? 0),

            // Helpful for frontend display
            'is_reply' => (bool) ($isArray ? $this->resource['parent_id'] : $this->parent_id),
            'has_replies' => $this->when(
                $isArray 
                    ? isset($this->resource['replies_count']) || isset($this->resource['replies']) 
                    : isset($this->replies_count) || $this->relationLoaded('replies'),
                function() use ($isArray) {
                    if ($isArray) {
                        return isset($this->resource['replies_count']) 
                            ? $this->resource['replies_count'] > 0 
                            : (isset($this->resource['replies']) ? count($this->resource['replies']) > 0 : false);
                    } else {
                        return isset($this->replies_count)
                            ? $this->replies_count > 0
                            : $this->replies->count() > 0;
                    }
                }
            ),

            // Track if this is the user's own comment
            'is_own' => $this->when(auth()->check(), function() use ($isArray) {
                return auth()->id() === ($isArray ? $this->resource['user_id'] : $this->user_id);
            }, false),
        ];
    }
}
