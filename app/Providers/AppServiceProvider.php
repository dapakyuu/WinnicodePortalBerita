<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        // Daftar kategori untuk dropdown menu header
        View::composer('partials.header', function ($view) {
            $view->with('navCategories', once(fn () => Category::orderBy('name')->get(['id', 'name', 'slug'])));
        });

        View::composer('partials.footer', function ($view) {
            $view->with([
                'footerCategories' => Category::withCount('posts')->orderByDesc('posts_count')->take(6)->get(),
                'footerComments' => Comment::with(['user:id,name', 'post:id,slug'])->latest()->take(4)->get(),
                // Feed Instagram belum terhubung ke API Instagram, jadi memakai galeri dari desain
                'footerInstagram' => [
                    ['image' => 'images/posts/car-02.png'],
                    ['image' => 'images/tags/animal.png'],
                    ['image' => 'images/instagram/technology-02.png'],
                    ['image' => 'images/posts/food-07.png'],
                    ['image' => 'images/instagram/music-01.png', 'position' => '84.6% center'],
                    ['image' => 'images/instagram/dance-03.png'],
                    ['image' => 'images/instagram/animal-07.png'],
                    ['image' => 'images/instagram/dance-01.png'],
                    ['image' => 'images/instagram/sport-02.png'],
                ],
            ]);
        });
    }
}
