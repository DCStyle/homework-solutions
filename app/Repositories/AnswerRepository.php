<?php

namespace App\Repositories;

use App\Models\WikiAnswer;
use App\Models\WikiQuestion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AnswerRepository
{
    /**
     * Get all answers for a question
     *
     * @param WikiQuestion $question
     * @param int $limit
     * @return Collection
     */
    public function getForQuestion(WikiQuestion $question, int $limit = 0): Collection
    {
        $query = WikiAnswer::with('user:id,name')
            ->where('question_id', $question->id)
            ->orderBy('is_ai', 'desc') // AI answers first
            ->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get the AI-generated answer for a question
     *
     * @param WikiQuestion $question
     * @return WikiAnswer|null
     */
    public function getAiAnswer(WikiQuestion $question): ?WikiAnswer
    {
        return WikiAnswer::where('question_id', $question->id)
            ->where('is_ai', true)
            ->first();
    }

    /**
     * Store a new answer
     *
     * @param WikiQuestion $question
     * @param array $data
     * @param bool $isAi
     * @return WikiAnswer
     */
    public function store(WikiQuestion $question, array $data, bool $isAi = false): WikiAnswer
    {
        $answer = new WikiAnswer();
        $answer->question_id = $question->id;
        $answer->content = $data['content'];
        $answer->user_id = $isAi ? null : Auth::id();
        $answer->is_ai = $isAi;
        $answer->save();

        return $answer->fresh(['user']);
    }

    /**
     * Update an existing answer
     *
     * @param WikiAnswer $answer
     * @param array $data
     * @return WikiAnswer
     */
    public function update(WikiAnswer $answer, array $data): WikiAnswer
    {
        $answer->content = $data['content'];
        $answer->save();

        return $answer->fresh(['user']);
    }

    /**
     * Check if an answer exists for a question
     *
     * @param WikiQuestion $question
     * @return bool
     */
    public function hasAnswer(WikiQuestion $question): bool
    {
        return WikiAnswer::where('question_id', $question->id)->exists();
    }

    /**
     * Check the saved answer status for a question
     *
     * @param WikiQuestion $question
     * @return array
     */
    public function checkAnswerStatus(WikiQuestion $question): array
    {
        $answer = $this->getAiAnswer($question);

        if (!$answer) {
            return [
                'exists' => false
            ];
        }

        return [
            'exists' => true,
            'status' => $answer->status,
            'completed' => $answer->status === 'completed'
        ];
    }

    /**
     * Delete an answer
     *
     * @param WikiAnswer $answer
     * @return bool
     */
    public function delete(WikiAnswer $answer): bool
    {
        return $answer->delete();
    }
}
