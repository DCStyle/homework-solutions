<?php

namespace App\Services\Search;

use App\Services\Search\Transformers\ArticleSearchTransformer;
use App\Services\Search\Transformers\BookGroupSearchTransformer;
use App\Services\Search\Transformers\BookSearchTransformer;
use App\Services\Search\Transformers\PostSearchTransformer;
use App\Services\Search\Transformers\SearchTransformerInterface;
use App\Services\Search\Transformers\UserSearchTransformer;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Search\Transformers\DefaultSearchTransformer;

class SearchService
{
    private $transformers = [];

    public function __construct()
    {
        $this->registerTransformer('Book', new BookSearchTransformer());
        $this->registerTransformer('BookGroup', new BookGroupSearchTransformer());
        $this->registerTransformer('Article', new ArticleSearchTransformer());
        $this->registerTransformer('User', new UserSearchTransformer());
        $this->registerTransformer('Post', new PostSearchTransformer());
    }

    public function registerTransformer(string $model, SearchTransformerInterface $transformer)
    {
        $this->transformers[$model] = $transformer;
    }

    /**
     * @return array
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    public function search(string $model, array $config)
    {
        $modelClass = "App\\Models\\" . $model;
        if (!class_exists($modelClass)) {
            throw new \Exception("Model not found: {$model}");
        }

        $query = $modelClass::query();

        // Apply relationships if specified
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        // Apply search conditions
        $this->applySearch($query, $config);

        // Apply any additional constraints
        if (isset($config['constraints']) && is_callable($config['constraints'])) {
            $config['constraints']($query);
        }

        // Get results
        $results = $query->limit($config['limit'] ?? 20)->get();

        // Transform results
        return $this->transformResults($results, $model, $config);
    }

    private function applySearch(Builder $query, array $config)
    {
        $searchTerm = $config['term'];
        $fields = $config['fields'];

        $query->where(function ($q) use ($fields, $searchTerm) {
            foreach ($fields as $field) {
                // Handle nested relationships
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field);
                    $q->orWhereHas($relation, function ($subQuery) use ($column, $searchTerm) {
                        $subQuery->where($column, 'LIKE', "%{$searchTerm}%");
                    });
                } else {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            }
        });
    }

    private function transformResults($results, string $model, array $config)
    {
        $transformer = $this->transformers[$model] ?? new DefaultSearchTransformer(
            $config['title_field'] ?? 'name',
            $config['subtitle_field'] ?? 'description',
            $config['route_name'] ?? null,
            $config['is_admin'] ?? false
        );

        return $results->map(fn($item) => $transformer->transform($item, $config));
    }
}
