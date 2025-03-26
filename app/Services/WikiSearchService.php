<?php

namespace App\Services;

use App\Models\WikiQuestion;
use App\Models\WikiQuestionEmbedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class WikiSearchService
{
    protected WikiAIService $aiService;

    public function __construct(WikiAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Search for questions based on a query.
     */
    public function search(string $query, array $options = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $options = array_merge([
            'threshold' => 0.7,
            'limit' => 10,
            'category_id' => null,
            'book_group_id' => null,
            'page' => null,
        ], $options);

        // Currently vector search not available

//        // Check if vector search is enabled and available
//        if ($this->isVectorSearchAvailable()) {
//            return $this->vectorSearch($query, $options);
//        }

        // Fallback to basic search
        return $this->basicSearch($query, $options);
    }

    /**
     * Perform a vector search using embeddings.
     */
    protected function vectorSearch(string $query, array $options): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Generate embedding for the search query
        $queryEmbedding = $this->aiService->generateEmbeddingForText($query);

        if (empty($queryEmbedding)) {
            // Fallback to basic search if embedding generation fails
            return $this->basicSearch($query, $options);
        }

        $threshold = $options['threshold'];
        $limit = $options['limit'];

        // Build the base query
        $baseQuery = WikiQuestion::published()
            ->with(['user', 'category', 'embedding']);

        // Apply category filter if specified
        if (!empty($options['category_id'])) {
            $baseQuery->where('category_id', $options['category_id']);
        }

        // Apply book group filter if specified
        if (!empty($options['book_group_id'])) {
            $baseQuery->where('book_group_id', $options['book_group_id']);
        }

        // Get questions with embeddings
        $questions = $baseQuery->get();

        $results = $questions->filter(function ($question) {
            return $question->embedding !== null;
        })->map(function ($question) use ($queryEmbedding) {
            $embedArray = $this->getEmbeddingArray($question->embedding->embedding);

            if (empty($embedArray)) {
                $question->similarity = 0;
                return $question;
            }

            $question->similarity = $this->cosineSimilarity($queryEmbedding, $embedArray);
            return $question;
        })->filter(function ($question) use ($threshold) {
            return $question->similarity >= $threshold;
        })->sortByDesc('similarity')->values();

        // Create a custom paginator
        $perPage = $limit;
        $page = $options['page'] ?: request()->get('page', 1);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $results->forPage($page, $perPage),
            $results->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0;
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($a as $i => $valueA) {
            $valueB = $b[$i] ?? 0;
            $dotProduct += $valueA * $valueB;
            $magnitudeA += $valueA * $valueA;
            $magnitudeB += $valueB * $valueB;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Convert embedding from string/JSON to array.
     */
    protected function getEmbeddingArray($embedding): array
    {
        if (is_array($embedding)) {
            return $embedding;
        }

        if (is_string($embedding)) {
            $decoded = json_decode($embedding, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Perform a basic keyword search - Optimized for performance.
     */
    protected function basicSearch(string $query, array $options): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Break the query into keywords for better matching
        $keywords = explode(' ', $query);
        $keywordConditions = [];

        $questionQuery = WikiQuestion::published()->with(['user', 'category']);

        // Build a more flexible search condition
        $questionQuery->where(function ($q) use ($query, $keywords) {
            // First try exact phrase match (higher relevance)
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");

            // Then try individual keyword matches
            foreach ($keywords as $keyword) {
                if (strlen($keyword) >= 3) { // Only use keywords with 3+ characters
                    $q->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%");
                }
            }
        });

        // Apply category filter if specified
        if (!empty($options['category_id'])) {
            $questionQuery->where('category_id', $options['category_id']);
        }

        // Apply book group filter if specified
        if (!empty($options['book_group_id'])) {
            $questionQuery->where('book_group_id', $options['book_group_id']);
        }

        // Order by relevance (created date for now) and then by views
        $questionQuery->orderBy('created_at', 'desc')
            ->orderBy('views', 'desc');

        return $questionQuery->paginate($options['limit'] ?? 10);
    }

    /**
     * Check if vector search is available.
     */
    protected function isVectorSearchAvailable(): bool
    {
        try {
            // Check if we have any embeddings
            return WikiQuestionEmbedding::count() > 0;
        } catch (\Exception $e) {
            // Database error
            return false;
        }
    }

    /**
     * Find related questions based on a given question.
     */
    public function findRelated(WikiQuestion $question, int $limit = 5): Collection
    {
        // Try to find related questions using vector similarity if available
        if ($this->isVectorSearchAvailable() && $question->embedding) {
            $questionEmbedding = $this->getEmbeddingArray($question->embedding->embedding);

            if (empty($questionEmbedding)) {
                // Fallback to category-based related questions
                return $this->getRelatedByCategory($question, $limit);
            }

            // Get all questions with embeddings
            $questions = WikiQuestion::published()
                ->with('embedding')
                ->where('id', '!=', $question->id)
                ->get();

            // Calculate similarities and sort
            $related = $questions->filter(function ($q) {
                return $q->embedding !== null;
            })->map(function ($q) use ($questionEmbedding) {
                $embedArray = $this->getEmbeddingArray($q->embedding->embedding);

                if (empty($embedArray)) {
                    $q->similarity = 0;
                    return $q;
                }

                $q->similarity = $this->cosineSimilarity($questionEmbedding, $embedArray);
                return $q;
            })->sortByDesc('similarity')->take($limit)->values();

            return $related;
        }

        // Fallback to category-based related questions
        return $this->getRelatedByCategory($question, $limit);
    }

    /**
     * Get related questions by category.
     */
    protected function getRelatedByCategory(WikiQuestion $question, int $limit): Collection
    {
        return WikiQuestion::published()
            ->where('category_id', $question->category_id)
            ->where('id', '!=', $question->id)
            ->orderBy('views', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get trending questions based on views and recency.
     */
    public function getTrendingQuestions(int $limit = 10): Collection
    {
        return WikiQuestion::published()
            ->orderByRaw('(views / (TIMESTAMPDIFF(HOUR, created_at, NOW()) + 1)) DESC')
            ->limit($limit)
            ->get();
    }
}
