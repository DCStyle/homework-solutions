<?php

namespace App\Events\Wiki;

use App\Models\WikiComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The comment instance.
     *
     * @var WikiComment
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param WikiComment $comment
     * @return void
     */
    public function __construct(WikiComment $comment)
    {
        $this->comment = $comment;
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
