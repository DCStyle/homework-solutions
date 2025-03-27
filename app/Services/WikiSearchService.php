<?php

namespace App\Services;

use App\Models\WikiQuestion;
use App\Models\WikiQuestionEmbedding;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WikiSearchService
{
    /**
     * Search for questions based on query and options
     *
     * @param string $query
     * @param array $options
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $options = []): LengthAwarePaginator
    {
        // Extract options with defaults
        $categoryId = $options['category_id'] ?? null;
        $bookGroupId = $options['book_group_id'] ?? null;
        $threshold = $options['threshold'] ?? 0.5;
        $limit = $options['limit'] ?? 10;
        $page = $options['page'] ?? 1;
        $useBasicSearch = $options['use_basic_search'] ?? false;

        try {
            // Use vector search if available and not using basic search
            if (!$useBasicSearch && $this->hasEmbeddings()) {
                return $this->vectorSearch($query, $categoryId, $bookGroupId, $threshold, $limit, $page);
            } else {
                // Fall back to basic search
                return $this->basicSearch($query, $categoryId, $bookGroupId, $limit, $page);
            }
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage(), [
                'query' => $query,
                'options' => $options,
                'trace' => $e->getTraceAsString()
            ]);

            // Always fall back to basic search in case of error
            return $this->basicSearch($query, $categoryId, $bookGroupId, $limit, $page);
        }
    }

    /**
     * Perform vector-based semantic search
     *
     * @param string $query
     * @param int|null $categoryId
     * @param int|null $bookGroupId
     * @param float $threshold
     * @param int $limit
     * @param int $page
     * @return LengthAwarePaginator
     */
    protected function vectorSearch(string $query, ?int $categoryId, ?int $bookGroupId, float $threshold, int $limit, int $page): LengthAwarePaginator
    {
        // First, generate embedding for the query
        $embedding = $this->generateEmbeddingForQuery($query);

        if (empty($embedding)) {
            return $this->basicSearch($query, $categoryId, $bookGroupId, $limit, $page);
        }

        // Build the base query
        $baseQuery = DB::table('wiki_questions')
            ->join('wiki_question_embeddings', 'wiki_questions.id', '=', 'wiki_question_embeddings.question_id')
            ->where('wiki_questions.status', 'published');

        // Apply category filter if specified
        if ($categoryId) {
            $baseQuery->where('wiki_questions.category_id', $categoryId);
        }

        // Apply book group filter if specified
        if ($bookGroupId) {
            $baseQuery->where('wiki_questions.book_group_id', $bookGroupId);
        }

        // Calculate cosine similarity with the query embedding
        $embeddingStr = json_encode($embedding);
        $baseQuery->selectRaw('wiki_questions.*,
            1 - (wiki_question_embeddings.embedding <=> ?::vector) as similarity',
            [$embeddingStr]);

        // Apply similarity threshold
        $baseQuery->where(DB::raw('1 - (wiki_question_embeddings.embedding <=> ?::vector)'), '>=', $threshold)
            ->orderBy('similarity', 'desc');

        // Get total count for pagination
        $total = $baseQuery->count();

        // Execute query with pagination
        $results = $baseQuery
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Load the actual WikiQuestion models
        $questionIds = $results->pluck('id')->toArray();
        $questions = WikiQuestion::with(['user:id,name', 'category:id,name,slug'])
            ->whereIn('id', $questionIds)
            ->get();

        // Order questions based on similarity ranking
        $orderedQuestions = collect();
        foreach ($results as $result) {
            $question = $questions->firstWhere('id', $result->id);
            if ($question) {
                $question->similarity = $result->similarity;
                $orderedQuestions->push($question);
            }
        }

        // Create paginator manually
        return new LengthAwarePaginator(
            $orderedQuestions,
            $total,
            $limit,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Perform basic keyword search when vector search is not available
     *
     * @param string $query
     * @param int|null $categoryId
     * @param int|null $bookGroupId
     * @param int $limit
     * @param int $page
     * @return LengthAwarePaginator
     */
    protected function basicSearch(string $query, ?int $categoryId, ?int $bookGroupId, int $limit, int $page): LengthAwarePaginator
    {
        $searchQuery = WikiQuestion::with(['user:id,name', 'category:id,name,slug'])
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            });

        // Apply category filter if specified
        if ($categoryId) {
            $searchQuery->where('category_id', $categoryId);
        }

        // Apply book group filter if specified
        if ($bookGroupId) {
            $searchQuery->where('book_group_id', $bookGroupId);
        }

        // Order by relevance (first title matches, then content matches, then newest)
        $searchQuery->orderByRaw("
            CASE
                WHEN title LIKE ? THEN 1
                WHEN title LIKE ? THEN 2
                WHEN content LIKE ? THEN 3
                ELSE 4
            END", [
            "{$query}%",   // Title starts with query (highest priority)
            "%{$query}%",  // Title contains query
            "%{$query}%",  // Content contains query
        ])
            ->orderBy('created_at', 'desc');

        return $searchQuery->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Find related questions based on a seed question
     *
     * @param WikiQuestion $question
     * @param int $limit
     * @return Collection
     */
    public function findRelated(WikiQuestion $question, int $limit = 5): Collection
    {
        try {
            // If the question has an embedding, use vector similarity search
            if ($question->embedding) {
                // Fix: Check if embedding is already an array or needs to be decoded
                $embedding = is_array($question->embedding->embedding) 
                    ? $question->embedding->embedding 
                    : json_decode($question->embedding->embedding, true);

                if (!empty($embedding)) {
                    $embeddingStr = json_encode($embedding);

                    $relatedIds = DB::table('wiki_questions')
                        ->join('wiki_question_embeddings', 'wiki_questions.id', '=', 'wiki_question_embeddings.question_id')
                        ->where('wiki_questions.status', 'published')
                        ->where('wiki_questions.id', '!=', $question->id)
                        ->selectRaw('wiki_questions.id, 1 - (wiki_question_embeddings.embedding <=> ?::vector) as similarity', [$embeddingStr])
                        ->orderBy('similarity', 'desc')
                        ->limit($limit)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($relatedIds)) {
                        return WikiQuestion::with(['user:id,name', 'category:id,name,slug'])
                            ->whereIn('id', $relatedIds)
                            ->get();
                    }
                }
            }

            // Fall back to category and book group based matching
            return WikiQuestion::with(['user:id,name', 'category:id,name,slug'])
                ->published()
                ->where('id', '!=', $question->id)
                ->where(function ($query) use ($question) {
                    $query->where('category_id', $question->category_id);

                    if ($question->book_group_id) {
                        $query->orWhere('book_group_id', $question->book_group_id);
                    }
                })
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get();

        } catch (\Exception $e) {
            Log::error('Error finding related questions: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Final fallback to simply get newest questions
            return WikiQuestion::with(['user:id,name', 'category:id,name,slug'])
                ->published()
                ->where('id', '!=', $question->id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }
    }

    /**
     * Generate embedding vector for a search query
     *
     * @param string $query
     * @return array|null
     */
    protected function generateEmbeddingForQuery(string $query): ?array
    {
        try {
            // Use the existing AI service to generate embedding
            $wikiAIService = app(WikiAIService::class);
            return $wikiAIService->generateEmbeddingForText($query);
        } catch (\Exception $e) {
            Log::error('Error generating embedding for query: ' . $e->getMessage(), [
                'query' => $query,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Check if embeddings are available in the system
     *
     * @return bool
     */
    protected function hasEmbeddings(): bool
    {
        return WikiQuestionEmbedding::count() > 0;
    }
}
