<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Services\ExternalNewsService;
use App\Services\StandingsService;
use App\Services\WeatherService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(StandingsService $standings, WeatherService $weather, ExternalNewsService $externalNews): View
    {
        // Tag populer = kategori yang paling banyak beritanya
        $tags = Category::withCount(['posts' => fn ($q) => $q->published()])
            ->orderByDesc('posts_count')
            ->take(10)
            ->get();

        // Slider: berita pilihan, dilengkapi berita terbaru bila kurang dari 5
        $slides = Post::published()->forCard()->where('is_featured', true)->latest('published_at')->take(5)->get();
        if ($slides->count() < 5) {
            $slides = $slides->concat(
                Post::published()->forCard()->whereNotIn('id', $slides->pluck('id'))->latest('published_at')->take(5 - $slides->count())->get()
            );
        }

        $popularPosts = Post::published()->forCard()->orderByDesc('views')->take(8)->get();
        $newPosts = Post::published()->forCard()->latest('published_at')->take(6)->get();

        $forecast = $weather->forecast();

        return view('pages.home', [
            'tags' => $tags,
            'slides' => $slides,
            'popularPosts' => $popularPosts,
            'leagues' => $standings->leagues(),
            'newPosts' => $newPosts,
            'weather' => $forecast['main'],
            'cities' => $forecast['cities'],
            'otherNews' => $externalNews->latest(4),
            'otherNewsUrl' => $externalNews->sourceUrl(),
        ]);
    }
}
