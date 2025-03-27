<?php

namespace App\Repositories;

use App\Models\WikiQuestion;
use App\Models\WikiQuestionEmbedding;
use App\Services\WikiAIService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuestionRepository
{
    protected $aiService;

    public function __construct(WikiAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get a paginated list of published questions
     *
     * @param int $limit
     * @param array $relations
     * @param string $orderBy
     * @param string $direction
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $limit = 10, array $relations = [], string $orderBy = 'created_at', string $direction = 'desc'): LengthAwarePaginator
    {
        $query = WikiQuestion::published();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->orderBy($orderBy, $direction)->paginate($limit);
    }

    /**
     * Get latest published questions
     *
     * @param int $limit
     * @param array $relations
     * @return Collection
     */
    public function getLatest(int $limit = 5, array $relations = []): Collection
    {
        $query = WikiQuestion::published();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Get trending questions (most viewed)
     *
     * @param int $limit
     * @param array $relations
     * @return Collection
     */
    public function getTrending(int $limit = 5, array $relations = []): Collection
    {
        $query = WikiQuestion::published();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->orderBy('views', 'desc')->limit($limit)->get();
    }

    /**
     * Find a question by its slug
     *
     * @param string $slug
     * @param array $relations
     * @return WikiQuestion|null
     */
    public function findBySlug(string $slug, array $relations = []): ?WikiQuestion
    {
        $query = WikiQuestion::where('slug', $slug);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Store a new question
     *
     * @param array $data
     * @return WikiQuestion
     */
    public function store(array $data): WikiQuestion
    {
        // Generate title using AI if not provided
        if (!isset($data['title']) || empty($data['title'])) {
            $categoryName = null;
            $bookGroupName = null;

            if (isset($data['category_id'])) {
                $category = \App\Models\Category::find($data['category_id']);
                $categoryName = $category ? $category->name : null;
            }

            if (isset($data['book_group_id']) && $data['book_group_id']) {
                $bookGroup = \App\Models\BookGroup::find($data['book_group_id']);
                $bookGroupName = $bookGroup ? $bookGroup->name : null;
            }

            $data['title'] = $this->aiService->generateQuestionTitle(
                $data['content'],
                $categoryName,
                $bookGroupName
            );
        }

        // Create the question
        $question = new WikiQuestion();
        $question->fill($data);
        $question->user_id = Auth::id();
        $question->status = 'pending';
        $question->save();

        // Generate embedding for the question
        try {
            $this->generateEmbedding($question);
        } catch (\Exception $e) {
            Log::error('Error generating embedding for new question: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the creation process if embedding fails
        }

        return $question->fresh();
    }

    /**
     * Update an existing question
     *
     * @param WikiQuestion $question
     * @param array $data
     * @return WikiQuestion
     */
    public function update(WikiQuestion $question, array $data): WikiQuestion
    {
        $question->fill($data);
        $question->save();

        // Regenerate embedding if content changed
        if (isset($data['content']) && $question->wasChanged('content')) {
            try {
                $this->generateEmbedding($question);
            } catch (\Exception $e) {
                Log::error('Error regenerating embedding for updated question: ' . $e->getMessage(), [
                    'question_id' => $question->id,
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't fail the update process if embedding fails
            }
        }

        return $question->fresh();
    }

    /**
     * Update question status
     *
     * @param WikiQuestion $question
     * @param string $status
     * @return WikiQuestion
     */
    public function updateStatus(WikiQuestion $question, string $status): WikiQuestion
    {
        $validStatuses = ['pending', 'published', 'rejected'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $question->status = $status;
        $question->save();

        return $question->fresh();
    }

    /**
     * Increment view count for a question
     *
     * @param WikiQuestion $question
     * @return void
     */
    public function incrementViews(WikiQuestion $question): void
    {
        $question->increment('views');
    }

    /**
     * Generate embedding for a question
     *
     * @param WikiQuestion $question
     * @return WikiQuestionEmbedding
     */
    protected function generateEmbedding(WikiQuestion $question): WikiQuestionEmbedding
    {
        $text = $question->title . ' ' . strip_tags($question->content);
        $embedding = $this->aiService->generateEmbeddingForText($text);

        if (empty($embedding)) {
            throw new \Exception('Failed to generate embedding');
        }

        return WikiQuestionEmbedding::updateOrCreate(
            ['question_id' => $question->id],
            ['embedding' => $embedding]
        );
    }

    /**
     * Find questions by category
     *
     * @param int $categoryId
     * @param int $limit
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function findByCategory(int $categoryId, int $limit = 10, array $relations = []): LengthAwarePaginator
    {
        $query = WikiQuestion::published()
            ->where('category_id', $categoryId);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    /**
     * Find questions by book group
     *
     * @param int $bookGroupId
     * @param int $limit
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function findByBookGroup(int $bookGroupId, int $limit = 10, array $relations = []): LengthAwarePaginator
    {
        $query = WikiQuestion::published()
            ->where('book_group_id', $bookGroupId);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
}
