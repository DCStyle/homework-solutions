<?php

namespace App\Services\Search\Transformers;

use Illuminate\Support\Str;

class PostSearchTransformer implements SearchTransformerInterface
{
    public function transform($post, array $config = []): array
    {
        $isAdmin = $config['is_admin'] ?? false;
        $routeName = $isAdmin
            ? 'admin.posts.edit'
            : 'posts.show';

        return [
            'label' => 'Bài viết',
            'id' => $post->id,
            'title' => $post->title,
            'subtitle' => Str::limit(strip_tags($post->content), 100),
            'url' => route($routeName, $isAdmin ? $post : $post->slug),
            'category' => $post->chapter?->name,
            'created_at' => $post->created_at->format('Y-m-d')
        ];
    }
}
