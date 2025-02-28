<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;

class CleanupPostTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:cleanup-titles {--dry-run : Run without deleting posts} {--force : Force delete posts without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and delete posts with problematic titles (ending with >)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        
        $this->info('Searching for posts with titles ending with ">" character...');
        
        // Get all posts that might have '>' anywhere in the title
        $potentialPosts = Post::where('title', 'like', '%>%')
            ->limit(5000)
            ->get();
            
        // Filter to only those where '>' is at the end after trimming
        $matchedPosts = $potentialPosts->filter(function($post) {
            $trimmedTitle = rtrim($post->title);
            return substr($trimmedTitle, -1) === '>';
        });
        
        if ($matchedPosts->isEmpty()) {
            $this->info('No posts found with titles ending with ">" character.');
            return 0;
        }
        
        $count = $matchedPosts->count();
        $this->info("Found {$count} posts with titles ending with '>' character:");
        
        // Display a table with the posts
        $headers = ['ID', 'Title', 'Slug'];
        $rows = [];
        
        foreach ($matchedPosts as $post) {
            $rows[] = [
                $post->id,
                $post->title,
                $post->slug
            ];
        }
        
        $this->table($headers, $rows);
        
        if ($isDryRun) {
            $this->info('Dry run completed. No posts were deleted.');
            return 0;
        }
        
        // Confirm deletion unless --force is used
        if (!$isForce && !$this->confirm('Do you want to delete these posts?')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        // Delete the posts
        $deletedCount = 0;
        foreach ($matchedPosts as $post) {
            try {
                $post->delete();
                $deletedCount++;
                $this->line("Deleted post ID: {$post->id}");
            } catch (\Exception $e) {
                $this->error("Failed to delete post ID {$post->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("Successfully deleted {$deletedCount} out of {$count} posts.");
        
        return 0;
    }
}
