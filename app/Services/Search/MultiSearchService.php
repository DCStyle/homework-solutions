<?php

namespace App\Services\Search;

class MultiSearchService
{
    private $searchService;
    private $searchableModels = [
        'Book' => [
            'label' => 'Sách',
            'fields' => ['name', 'description'],
            'limit' => 10,
            'route_name' => 'books.show',
        ],
        'Chapter' => [
            'label' => 'Chương',
            'fields' => ['name', 'description'],
            'limit' => 10,
            'route_name' => 'chapters.show',
        ],
        'Post' => [
            'label' => 'Bài viết',
            'fields' => ['title', 'content'],
            'limit' => 10,
            'title_field' => 'title',
            'subtitle_field' => 'content',
            'route_name' => 'posts.show',
        ],
        'Article' => [
            'label' => 'Tin tức',
            'fields' => ['title', 'content'],
            'limit' => 10,
            'title_field' => 'title',
            'subtitle_field' => 'content',
            'route_name' => 'articles.show',
        ],
    ];

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(string $term, bool $isAdmin = false, array $models = null)
    {
        $results = [];
        $searchableModels = $models ? array_intersect_key($this->searchableModels, array_flip($models)) : $this->searchableModels;

        foreach ($searchableModels as $model => $config) {
            try {
                $modelResults = $this->searchService->search($model, [
                    'term' => $term,
                    'fields' => $config['fields'],
                    'limit' => $config['limit'],
                    'is_admin' => $isAdmin,
                    'with' => $this->getRelationships($model),
                    'constraints' => $this->getConstraints($model),
                    'title_field' => $config['title_field'] ?? 'name',
                    'subtitle_field' => $config['subtitle_field'] ?? 'description',
                    'route_name' => $config['route_name'] ?? null,
                ]);

                if ($modelResults->isNotEmpty()) {
                    $results[$model] = [
                        'label' => $config['label'] ?? str($model)->plural(),
                        'results' => $modelResults
                    ];
                }
            } catch (\Exception $e) {
                \Log::error("Error searching {$model}: " . $e->getMessage());
                continue;
            }
        }

        return $results;
    }

    private function getRelationships(string $model): array
    {
        return match($model) {
            'Article' => ['category', 'tags'],
            'User' => ['role', 'profile'],
            'Book' => ['group'],
            'Chapter' => ['book'],
            'Post' => ['chapter'],
            default => []
        };
    }

    private function getConstraints(string $model): ?callable
    {
        return match($model) {
            default => null
        };
    }
}
