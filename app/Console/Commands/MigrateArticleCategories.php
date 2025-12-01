<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateArticleCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-article-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Migrating article categories to pivot table...');
        
        $articles = \App\Models\Article::whereNotNull('category_id')
            ->whereDoesntHave('categories')
            ->get();
        
        $count = 0;
        foreach ($articles as $article) {
            if ($article->category_id) {
                $categoryExists = \App\Models\ArticleCategory::where('category_id', $article->category_id)->exists();
                if ($categoryExists) {
                    $article->categories()->attach($article->category_id);
                    $count++;
                }
            }
        }
        
        $this->info("Migrated {$count} articles to pivot table.");
        return 0;
    }
}
