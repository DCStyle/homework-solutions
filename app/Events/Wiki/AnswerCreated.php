<?php

namespace App\Events\Wiki;

use App\Models\WikiAnswer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The answer instance.
     *
     * @var WikiAnswer
     */
    public $answer;

    /**
     * Create a new event instance.
     *
     * @param WikiAnswer $answer
     * @return void
     */
    public function __construct(WikiAnswer $answer)
    {
        $this->answer = $answer;
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
