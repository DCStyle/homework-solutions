<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'views' => $this->views,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // Category information
            'category' => $this->when($this->category, function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            'category_slug' => $this->category ? $this->category->slug : 'uncategorized',

            // Book group information
            'book_group' => $this->when($this->bookGroup, function() {
                return [
                    'id' => $this->bookGroup->id,
                    'name' => $this->bookGroup->name,
                    'slug' => $this->bookGroup->slug,
                ];
            }),

            // User information
            'user' => $this->when($this->user, function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),

            // Conditional relationships
            'answers_count' => $this->when(isset($this->answers_count), $this->answers_count),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),

            // Include answers when available
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),

            // Include top-level comments when available
            'comments' => CommentResource::collection(
                $this->whenLoaded('comments', function() {
                    return $this->comments->whereNull('parent_id');
                })
            ),

            // Similarity score for search results
            'similarity' => $this->when(isset($this->similarity), $this->similarity),

            // URLs for frontend
            'url' => route('wiki.show', [$this->category ? $this->category->slug : 'uncategorized', $this->slug]),
        ];
    }
}
