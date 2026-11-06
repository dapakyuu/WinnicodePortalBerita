<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Report;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // Pembaca per hari, 30 hari terakhir
        $since = now()->subDays(29)->startOfDay();
        $daily = PostView::where('created_at', '>=', $since)
            ->get(['created_at'])
            ->countBy(fn ($view) => $view->created_at->format('Y-m-d'));

        $viewsChart = ['labels' => [], 'values' => []];
        for ($i = 0; $i < 30; $i++) {
            $day = $since->copy()->addDays($i);
            $viewsChart['labels'][] = $day->format('d M');
            $viewsChart['values'][] = $daily[$day->format('Y-m-d')] ?? 0;
        }

        $categories = Category::withCount('posts')->orderByDesc('posts_count')->get();

        return view('admin.dashboard', [
            'stats' => [
                'posts' => Post::count(),
                'published' => Post::published()->count(),
                'users' => User::count(),
                'comments' => Comment::count(),
                'views' => (int) Post::sum('views'),
                'openReports' => Report::whereNull('resolved_at')->count(),
                'newUsers' => User::where('created_at', '>=', now()->subDays(30))->count(),
                'views30' => array_sum($viewsChart['values']),
            ],
            'viewsChart' => $viewsChart,
            'categoryChart' => [
                'labels' => $categories->pluck('name'),
                'values' => $categories->pluck('posts_count'),
            ],
            'latestPosts' => Post::with(['user:id,name', 'category:id,name'])->latest()->take(6)->get(),
            'topPosts' => Post::published()->orderByDesc('views')->take(5)->get(['id', 'title', 'slug', 'views']),
            'latestComments' => Comment::with(['user:id,name,avatar', 'post:id,title,slug'])->latest()->take(5)->get(),
            'latestUsers' => User::latest()->take(8)->get(),
        ]);
    }
}
