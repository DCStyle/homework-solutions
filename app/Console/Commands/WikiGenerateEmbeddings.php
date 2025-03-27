<?php

namespace App\Console\Commands;

use App\Models\WikiQuestion;
use App\Models\WikiQuestionEmbedding;
use App\Services\WikiAIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WikiGenerateEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wiki:generate-embeddings
                            {--limit=50 : Maximum number of questions to process}
                            {--force : Force regeneration of existing embeddings}
                            {--status=published : Question status to filter (all,published,pending,rejected)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for wiki questions for vector search';

    /**
     * The AI service instance for generating embeddings
     *
     * @var WikiAIService
     */
    protected $aiService;

    /**
     * Create a new command instance.
     *
     * @param  WikiAIService  $aiService
     * @return void
     */
    public function __construct(WikiAIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int)$this->option('limit');
        $force = $this->option('force');
        $status = $this->option('status');

        $this->info("Starting embedding generation for wiki questions...");
        $this->info("Limit: $limit, Force: " . ($force ? 'Yes' : 'No') . ", Status: $status");

        try {
            // Build query for questions
            $query = WikiQuestion::with('embedding');

            // Filter by status if not "all"
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Skip questions that already have embeddings unless force is true
            if (!$force) {
                $query->whereDoesntHave('embedding');
            }

            // Get total count for progress bar
            $total = $query->count();
            $this->info("Found $total questions to process");

            // Apply limit
            if ($limit > 0) {
                $query->limit($limit);
            }

            // Get questions
            $questions = $query->get();

            // Initialize progress bar
            $progressBar = $this->output->createProgressBar(count($questions));
            $progressBar->start();

            $success = 0;
            $failed = 0;

            // Process each question
            foreach ($questions as $question) {
                try {
                    // Generate embedding for the question
                    $text = $question->title . ' ' . strip_tags($question->content);
                    $embedding = $this->aiService->generateEmbeddingForText($text);

                    if (empty($embedding)) {
                        $this->error("Empty embedding returned for question ID {$question->id}");
                        $failed++;
                        $progressBar->advance();
                        continue;
                    }

                    // Save the embedding
                    WikiQuestionEmbedding::updateOrCreate(
                        ['question_id' => $question->id],
                        ['embedding' => $embedding]
                    );

                    $success++;
                } catch (\Exception $e) {
                    Log::error('Error generating embedding for question: ' . $e->getMessage(), [
                        'question_id' => $question->id,
                        'trace' => $e->getTraceAsString()
                    ]);

                    $this->error("Error processing question ID {$question->id}: {$e->getMessage()}");
                    $failed++;
                }

                // Advanced progress
                $progressBar->advance();

                // Add a small delay to avoid rate limiting
                usleep(100000); // 100ms
            }

            // Finish progress bar
            $progressBar->finish();
            $this->newLine(2);

            // Show results
            $this->info("Embedding generation complete!");
            $this->info("Successfully processed: $success questions");
            $this->info("Failed: $failed questions");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating embeddings: {$e->getMessage()}");
            Log::error('Error in WikiGenerateEmbeddings command: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
