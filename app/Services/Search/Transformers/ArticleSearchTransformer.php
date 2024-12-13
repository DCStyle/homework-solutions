<?php

namespace App\Services\Search\Transformers;

use Illuminate\Support\Str;

class ArticleSearchTransformer implements SearchTransformerInterface
{
    public function transform($article, array $config = []): array
    {
        $isAdmin = $config['is_admin'] ?? false;
        $routeName = $isAdmin
            ? 'admin.articles.edit'
            : 'articles.show';

        return [
            'label' => 'Tin tức',
            'id' => $article->id,
            'title' => $article->title,
            'subtitle' => Str::limit(strip_tags($article->content), 100),
            'url' => route($routeName, $isAdmin ? $article : $article->slug),
            'category' => $article->category?->name,
            'created_at' => $article->created_at->format('Y-m-d')
        ];
    }
}
