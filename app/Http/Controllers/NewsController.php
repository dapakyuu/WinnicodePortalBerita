<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostRating;
use App\Models\PostView;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Halaman berita: daftar (New Post / Trending / pencarian), kategori, dan detail,
 * beserta aksi komentar, rating, bookmark, dan laporan.
 */
class NewsController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $sort = $this->sort($request);
        $search = trim((string) $request->query('q'));

        $posts = Post::published()->forCard()
            ->search($search)
            ->sorted($sort)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $title = match (true) {
            $search !== '' => 'Search : ' . $search,
            $sort === 'popular' => 'Trendings',
            $sort === 'top' => 'Top Post',
            default => 'New Post',
        };

        return view('news.index', compact('posts', 'title', 'sort'));
    }

    public function category(Request $request, Category $category): View
    {
        $sort = $this->sort($request);

        $posts = $category->posts()->published()->forCard()
            ->sorted($sort)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('news.index', [
            'title' => 'Category : ' . $category->name,
            'category' => $category,
            'sort' => $sort,
            'posts' => $posts,
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        $user = $request->user();
        abort_unless($post->published_at?->isPast() || $post->canBeManagedBy($user), 404);

        $this->recordView($request, $post);

        $post->load(['user', 'category', 'tags'])
            ->loadCount(['comments', 'ratings'])
            ->loadAvg('ratings', 'rating');

        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        $related = Post::published()->forCard()
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(8)
            ->get();
        if ($related->count() < 4) {
            $related = $related->concat(
                Post::published()->forCard()->whereKeyNot($post->id)->whereNotIn('id', $related->pluck('id'))
                    ->orderByDesc('views')->take(4 - $related->count())->get()
            );
        }

        $topPosts = Post::published()->orderByDesc('views')->take(5)->get(['id', 'title', 'slug', 'image', 'video_url', 'published_at']);

        return view('pages.single', [
            'post' => $post,
            'comments' => $comments,
            'related' => $related,
            'topPosts' => $topPosts,
            'authorPostCount' => $post->user->posts()->published()->count(),
            'userRating' => $user ? PostRating::where('post_id', $post->id)->where('user_id', $user->id)->value('rating') : null,
        ]);
    }

    public function comment(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'parent_id' => ['nullable', 'integer', Rule::exists('comments', 'id')->where('post_id', $post->id)],
        ]);

        // Balasan selalu menempel ke komentar utama (maksimal 1 tingkat)
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parentId = Comment::find($parentId)->parent_id ?? $parentId;
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'body' => $data['comment'],
        ]);

        if (! empty($data['rating'])) {
            PostRating::updateOrCreate(
                ['post_id' => $post->id, 'user_id' => $request->user()->id],
                ['rating' => $data['rating']]
            );
        }

        return redirect()->to(route('news.show', $post) . '#comment-' . $comment->id)
            ->with('status', 'Komentar berhasil dikirim.');
    }

    public function destroyComment(Request $request, Comment $comment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->id === $comment->user_id || $user->isAdmin(), 403);

        $comment->delete();

        return back()->with('status', 'Komentar dihapus.');
    }

    public function bookmark(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $result = $request->user()->bookmarks()->toggle($post->id);
        $bookmarked = $result['attached'] !== [];

        if ($request->expectsJson()) {
            return response()->json(['bookmarked' => $bookmarked]);
        }

        return back()->with('status', $bookmarked ? 'Berita disimpan.' : 'Berita dihapus dari simpanan.');
    }

    public function report(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        Report::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
        ]);

        return back()->with('status', 'Terima kasih, laporan kamu sudah kami terima.');
    }

    /** Hitung 1 view per sesi untuk setiap berita. */
    private function recordView(Request $request, Post $post): void
    {
        $viewed = $request->session()->get('viewed_posts', []);
        if (in_array($post->id, $viewed, true)) {
            return;
        }

        $post->increment('views');
        PostView::create(['post_id' => $post->id, 'user_id' => $request->user()?->id, 'created_at' => now()]);
        $request->session()->push('viewed_posts', $post->id);
    }

    private function sort(Request $request): string
    {
        return in_array($request->query('sort'), ['new', 'popular', 'top'], true) ? $request->query('sort') : 'new';
    }
}
