<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FixYouTubeVideos extends Command
{
    protected $signature = 'fix:youtube-videos {--scan : Scan and store video IDs without making changes} {--process : Process posts using stored video IDs}';
    protected $description = 'Fix case sensitivity in YouTube video IDs';

    protected $csvPath = 'youtube_video_ids.csv';

    public function handle()
    {
        if (!$this->option('scan') && !$this->option('process')) {
            $this->error('Please specify either --scan or --process option');
            return;
        }

        if ($this->option('scan')) {
            $this->scanVideos();
        } else {
            $this->processVideos();
        }
    }

    protected function scanVideos()
    {
        $posts = Post::where('content', 'LIKE', '%youtube.com/embed/%')->get();
        $totalPosts = $posts->count();

        if ($totalPosts === 0) {
            $this->warn('No posts with YouTube embeds found.');
            return;
        }

        $this->info("Found {$totalPosts} posts with YouTube embeds.");

        $progress = $this->output->createProgressBar($totalPosts);
        $progress->start();

        $foundVideos = [];
        $processedPosts = 0;

        foreach ($posts as $post) {
            try {
                $this->newLine();
                $this->info("Scanning Post ID: {$post->id}");

                // Find all YouTube video IDs
                preg_match_all('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/i', $post->content, $matches);

                if (!empty($matches[1])) {
                    $this->line("Found " . count($matches[1]) . " YouTube video(s)");

                    foreach ($matches[1] as $videoId) {
                        $lowerVideoId = Str::lower($videoId);
                        if (!isset($foundVideos[$lowerVideoId])) {
                            $foundVideos[$lowerVideoId] = [
                                'original_id' => $videoId,
                                'correct_id' => '', // To be filled manually
                                'post_ids' => [$post->id]
                            ];
                        } else {
                            $foundVideos[$lowerVideoId]['post_ids'][] = $post->id;
                        }
                    }
                }

                $processedPosts++;
            } catch (\Exception $e) {
                $this->error("Error scanning post {$post->id}: " . $e->getMessage());
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);

        // Save to CSV
        $csvContent = "original_id,correct_id,post_ids\n";
        foreach ($foundVideos as $data) {
            $postIds = implode('|', array_unique($data['post_ids']));
            $csvContent .= "{$data['original_id']},{$data['correct_id']},{$postIds}\n";
        }

        Storage::put($this->csvPath, $csvContent);

        $this->info('=== Summary ===');
        $this->info("Total posts scanned: {$processedPosts}/{$totalPosts}");
        $this->info("Total unique video IDs found: " . count($foundVideos));
        $this->info("CSV file saved to: {$this->csvPath}");
        $this->info("Please edit the CSV file to add correct video IDs in the 'correct_id' column");
    }

    protected function processVideos()
    {
        if (!Storage::exists($this->csvPath)) {
            $this->error("CSV file not found. Please run --scan first.");
            return;
        }

        // Read and parse CSV
        $csvContent = Storage::get($this->csvPath);
        $lines = explode("\n", trim($csvContent));
        array_shift($lines); // Remove header

        $videoMappings = [];
        foreach ($lines as $line) {
            if (empty($line)) continue;

            list($originalId, $correctId, $postIds) = str_getcsv($line);
            if (!empty($correctId)) {
                $videoMappings[Str::lower($originalId)] = $correctId;
            }
        }

        if (empty($videoMappings)) {
            $this->warn("No correct video IDs found in CSV. Please fill in the 'correct_id' column.");
            return;
        }

        // Process posts
        $postIds = array_unique(explode('|', implode('|', array_column(array_map('str_getcsv', $lines), 2))));
        $posts = Post::whereIn('id', $postIds)->get();
        $totalPosts = $posts->count();

        $this->info("Processing {$totalPosts} posts with YouTube embeds.");

        $progress = $this->output->createProgressBar($totalPosts);
        $progress->start();

        $fixedVideos = 0;
        $processedPosts = 0;

        foreach ($posts as $post) {
            try {
                $this->newLine();
                $this->info("Processing Post ID: {$post->id}");

                $content = $post->content;
                $originalContent = $content;

                // Find and replace video IDs
                preg_match_all('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/i', $content, $matches);

                if (!empty($matches[1])) {
                    foreach ($matches[1] as $videoId) {
                        $lowerVideoId = Str::lower($videoId);
                        if (isset($videoMappings[$lowerVideoId]) && $videoMappings[$lowerVideoId] !== $videoId) {
                            $content = str_replace($videoId, $videoMappings[$lowerVideoId], $content);
                            $fixedVideos++;
                            $this->info("✓ Fixed video ID: {$videoId} → {$videoMappings[$lowerVideoId]}");
                        }
                    }

                    if ($content !== $originalContent) {
                        $post->content = $content;
                        $post->save();
                        $this->info("✓ Saved changes to post {$post->id}");
                    }
                }

                $processedPosts++;
            } catch (\Exception $e) {
                $this->error("Error processing post {$post->id}: " . $e->getMessage());
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);

        $this->info('=== Summary ===');
        $this->info("Total posts processed: {$processedPosts}/{$totalPosts}");
        $this->info("Total videos fixed: {$fixedVideos}");
    }
}
