<?php

namespace App\Events\Wiki;

use App\Models\WikiQuestion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The question instance.
     *
     * @var WikiQuestion
     */
    public $question;

    /**
     * The previous status.
     *
     * @var string
     */
    public $oldStatus;

    /**
     * The new status.
     *
     * @var string
     */
    public $newStatus;

    /**
     * Create a new event instance.
     *
     * @param WikiQuestion $question
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public function __construct(WikiQuestion $question, string $oldStatus, string $newStatus)
    {
        $this->question = $question;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
