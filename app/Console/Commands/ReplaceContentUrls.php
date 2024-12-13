<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReplaceContentUrls extends Command
{
    protected $signature = 'content:replace-urls';
    protected $description = 'Replace strings with current app base URL in all content';

    protected $models = [
        Article::class => ['content'],
        Category::class => ['description'],
        Book::class => ['description'],
        BookChapter::class => ['description'],
        BookGroup::class => ['description'],
        Post::class => ['content']
    ];

    public function handle()
    {
        $this->info('Starting URL replacement...');

        $searchStrings = ['https://img./'];
        $stringToReplace = 'https://img.loigiaihay.com/';

        try {
            foreach ($this->models as $model => $fields) {
                foreach ($fields as $field) {
                    foreach($searchStrings as $searchString) {
                        $tableName = (new $model)->getTable();

                        $count = DB::table($tableName)
                            ->whereRaw("LOWER({$field}) LIKE ?", ['%' . strtolower($searchString) . '%'])
                            ->update([
                                $field => DB::raw("REPLACE({$field}, '{$searchString}', '{$stringToReplace}')")
                            ]);

                        $this->info("Updated {$count} records in " . class_basename($model) . " ({$field})");
                    }
                }
            }

            $this->info('Successfully replaced URLs in all content.');
        } catch (\Exception $e) {
            $this->error('Error replacing URLs: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
