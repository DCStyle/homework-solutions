<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScanNanPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:scan-nan {--output=nan_posts.csv : The output CSV file path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all posts with exact content "nan" and output to CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning for posts with exact content "nan"...');
        
        // Find posts with exact 'nan' content
        $posts = Post::where('content', 'nan')->get();
        
        $count = $posts->count();
        $this->info("Found {$count} posts with exact content 'nan'");
        
        if ($count === 0) {
            $this->info('No posts found with exact content "nan"');
            return 0;
        }
        
        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['category_title', 'book_group_title', 'book_title', 'book_chapter_title', 'post_id', 'post_source_url'];
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        foreach ($posts as $post) {
            try {
                $chapter = $post->chapter;
                
                if (!$chapter) {
                    $this->error("Post ID {$post->id} has no chapter");
                    continue;
                }
                
                $book = $chapter->book;
                
                if (!$book) {
                    $this->error("Chapter ID {$chapter->id} has no book");
                    continue;
                }
                
                $bookGroup = $book->group;
                
                if (!$bookGroup) {
                    $this->error("Book ID {$book->id} has no book group");
                    continue;
                }
                
                $category = $bookGroup->category;
                
                if (!$category) {
                    $this->error("Book group ID {$bookGroup->id} has no category");
                    continue;
                }
                
                $csvData[] = [
                    $category->name,
                    $bookGroup->name,
                    $book->name,
                    $chapter->name,
                    $post->id,
                    $post->source_url ?? '',
                ];
                
            } catch (\Exception $e) {
                $this->error("Error processing post ID {$post->id}: {$e->getMessage()}");
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        // Create output file
        $outputPath = $this->option('output');
        $outputFullPath = storage_path('app/' . $outputPath);
        
        // Ensure directory exists
        $directory = dirname($outputFullPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        // Write CSV
        $file = fopen($outputFullPath, 'w');
        
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        $this->info("CSV file created successfully at: {$outputFullPath}");
        
        return 0;
    }
}
