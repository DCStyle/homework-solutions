<?php

namespace App\Events\Wiki;

use App\Models\WikiQuestion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The question instance.
     *
     * @var WikiQuestion
     */
    public $question;

    /**
     * Create a new event instance.
     *
     * @param WikiQuestion $question
     * @return void
     */
    public function __construct(WikiQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('wiki');
    }
}
