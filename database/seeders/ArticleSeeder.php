<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Smaller article set for faster SQLite demo
        Article::factory()
            ->count(8)
            ->make()
            ->each(function ($article) use ($users) {
                $article->author_id = $users->random()->user_id;
                $article->save();

                Comment::factory()
                    ->count(rand(1, 3))
                    ->make()
                    ->each(function ($comment) use ($article, $users) {
                        $comment->article_id = $article->article_id;
                        $comment->user_id = $users->random()->user_id;
                        $comment->save();

                        if (rand(0, 1)) {
                            Comment::factory()
                                ->count(rand(1, 2))
                                ->make()
                                ->each(function ($reply) use ($comment, $article, $users) {
                                    $reply->article_id = $article->article_id;
                                    $reply->user_id = $users->random()->user_id;
                                    $reply->parent_id = $comment->comment_id;
                                    $reply->save();
                                });
                        }
                    });
            });
    }
}
