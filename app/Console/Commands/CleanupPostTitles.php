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
    protected $signature = 'posts:cleanup-titles {--dry-run : Run without deleting posts} {--fix : Fix problematic titles instead of deleting posts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and fix/delete posts with problematic titles (ending with >, containing "- loigiaihay.com", HTML tags, or having excessive whitespace)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $fixMode = $this->option('fix');

        $this->info('Searching for posts with problematic titles...');
        
        // Check for posts with titles ending with '>'
        $this->info('Checking for titles ending with ">" character...');
        $potentialPosts = Post::where('title', 'like', '%>%')
            ->limit(5000)
            ->get();
            
        $endingWithArrow = $potentialPosts->filter(function ($post) {
            $trimmedTitle = rtrim($post->title);
            return substr($trimmedTitle, -1) === '>';
        });
        
        if ($endingWithArrow->count() > 0) {
            $this->info("Found {$endingWithArrow->count()} posts with titles ending with '>' character:");
            
            foreach ($endingWithArrow as $post) {
                $this->line("ID: {$post->id}, Title: \"{$post->title}\", Slug: \"{$post->slug}\"");
            }
            
            if (!$dryRun) {
                if ($fixMode) {
                    foreach ($endingWithArrow as $post) {
                        $oldTitle = $post->title;
                        $newTitle = rtrim($post->title, '>');
                        $newTitle = trim($newTitle);
                        
                        $post->title = $newTitle;
                        $post->save();
                        
                        $this->info("Fixed post ID: {$post->id}");
                        $this->line("  Old title: \"{$oldTitle}\"");
                        $this->line("  New title: \"{$newTitle}\"");
                    }
                    $this->info('Titles fixed successfully.');
                } elseif ($this->confirm('Do you want to delete these posts?')) {
                    foreach ($endingWithArrow as $post) {
                        $post->delete();
                        $this->info("Deleted post ID: {$post->id}");
                    }
                    $this->info('Posts deleted successfully.');
                } else {
                    $this->info('Operation cancelled.');
                }
            } else {
                $this->info('Dry run completed. No posts were modified or deleted.');
            }
        } else {
            $this->info("No posts found with titles ending with '>' character.");
        }
        
        // Check for posts with titles ending with "- loigiaihay.com"
        $this->info('Checking for titles ending with "- loigiaihay.com"...');
        $postsWithSourceSite = Post::where('title', 'like', '%- loigiaihay.com')
            ->limit(5000)
            ->get();
            
        if ($postsWithSourceSite->count() > 0) {
            $this->info("Found {$postsWithSourceSite->count()} posts with titles ending with '- loigiaihay.com':");
            
            foreach ($postsWithSourceSite as $post) {
                $this->line("ID: {$post->id}, Title: \"{$post->title}\", Slug: \"{$post->slug}\"");
            }
            
            if (!$dryRun) {
                if ($fixMode) {
                    foreach ($postsWithSourceSite as $post) {
                        $oldTitle = $post->title;
                        $newTitle = preg_replace('/\s*-\s*loigiaihay\.com\s*$/i', '', $post->title);
                        $newTitle = trim($newTitle);
                        
                        $post->title = $newTitle;
                        $post->save();
                        
                        $this->info("Fixed post ID: {$post->id}");
                        $this->line("  Old title: \"{$oldTitle}\"");
                        $this->line("  New title: \"{$newTitle}\"");
                    }
                    $this->info('Titles fixed successfully.');
                } elseif ($this->confirm('Do you want to delete these posts?')) {
                    foreach ($postsWithSourceSite as $post) {
                        $post->delete();
                        $this->info("Deleted post ID: {$post->id}");
                    }
                    $this->info('Posts deleted successfully.');
                } else {
                    $this->info('Operation cancelled.');
                }
            } else {
                $this->info('Dry run completed. No posts were modified or deleted.');
            }
        } else {
            $this->info("No posts found with titles ending with '- loigiaihay.com'.");
        }
        
        return Command::SUCCESS;
    }
}
