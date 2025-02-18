<?php

namespace App\Console\Commands;

use App\Models\PostAttachment;
use Illuminate\Console\Command;

class CleanOrphanedAttachments extends Command
{
    protected $signature = 'attachments:clean-orphaned';
    protected $description = 'Clean up orphaned post attachments';

    public function handle()
    {
        // Find attachments older than 24 hours that aren't associated with any post
        $orphanedAttachments = PostAttachment::whereNull('post_id')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($orphanedAttachments as $attachment) {
            // Delete the file
            Storage::disk('public')->delete('post-attachments/' . $attachment->filename);
            // Delete the record
            $attachment->delete();
        }

        $this->info("Cleaned up {$orphanedAttachments->count()} orphaned attachments.");
    }
}
