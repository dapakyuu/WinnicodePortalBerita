<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRating;
use App\Models\PostView;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Halaman profil: berita tersimpan, kirim/edit/hapus berita,
 * statistik berita sendiri, dan edit profil.
 */
class ProfileController extends Controller
{
    use HandlesUploads;

    public function marked(Request $request): View
    {
        $posts = $request->user()->bookmarks()->published()->forCard()
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return view('profile.marked', ['user' => $request->user(), 'posts' => $posts]);
    }

    /* ---------- Kirim / edit berita ---------- */

    public function create(Request $request): View
    {
        return view('profile.send-post', [
            'user' => $request->user(),
            'post' => new Post(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePost($request);

        $post = $request->user()->posts()->create([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => Post::uniqueSlug($data['title']),
            'content' => $data['content'],
            'video_url' => $data['type'] === 'video' ? $data['video_url'] : null,
            'image' => $request->hasFile('image') ? $this->upload($request->file('image'), 'posts') : null,
            'published_at' => now(),
        ]);
        $post->tags()->sync(Tag::idsFromString($data['tags'] ?? ''));

        return redirect()->route('news.show', $post)->with('status', 'Berita berhasil dipublikasikan.');
    }

    public function editPost(Request $request, Post $post): View
    {
        abort_unless($post->canBeManagedBy($request->user()), 403);

        return view('profile.send-post', [
            'user' => $request->user(),
            'post' => $post->load('tags'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function updatePost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->canBeManagedBy($request->user()), 403);
        $data = $this->validatePost($request, $post);

        if ($request->hasFile('image')) {
            $this->deleteUpload($post->image);
            $post->image = $this->upload($request->file('image'), 'posts');
        }

        $post->fill([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $post->title === $data['title'] ? $post->slug : Post::uniqueSlug($data['title'], $post->id),
            'content' => $data['content'],
            'video_url' => $data['type'] === 'video' ? $data['video_url'] : null,
        ])->save();
        $post->tags()->sync(Tag::idsFromString($data['tags'] ?? ''));

        return redirect()->route('news.show', $post)->with('status', 'Berita berhasil diperbarui.');
    }

    public function destroyPost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->canBeManagedBy($request->user()), 403);

        $this->deleteUpload($post->image);
        $post->delete();

        return redirect()->route('profile.posts')->with('status', 'Berita berhasil dihapus.');
    }

    /* ---------- Statistik ---------- */

    public function posts(Request $request): View
    {
        $user = $request->user();
        $postIds = $user->posts()->pluck('id');

        // Jumlah pembaca per bulan (8 bulan terakhir)
        $views = [];
        for ($i = 7; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $views[$month->format('F Y')] = PostView::whereIn('post_id', $postIds)
                ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
                ->count();
        }

        // Kepuasan pembaca per bulan (rata-rata rating, 9 bulan terakhir, baris terbaru di atas)
        $satisfaction = [];
        foreach ([[2, 1, 0], [5, 4, 3], [8, 7, 6]] as $row) {
            foreach ($row as $i) {
                $month = now()->startOfMonth()->subMonths($i);
                $avg = PostRating::whereIn('post_id', $postIds)
                    ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
                    ->avg('rating');
                $satisfaction[] = [
                    'month' => $month->format('F Y'),
                    'points' => $avg === null ? 0 : (int) round($avg * 6),
                    'mood' => match (true) {
                        $avg === null => 'none',
                        $avg >= 4.5 => 'great',
                        $avg >= 3.5 => 'good',
                        $avg >= 2.5 => 'ok',
                        default => 'bad',
                    },
                ];
            }
        }

        $myPosts = $user->posts()->with('category:id,name,slug')->latest('published_at')->paginate(8);

        return view('profile.posts', [
            'user' => $user,
            'chart' => $this->buildChart($views),
            'satisfaction' => $satisfaction,
            'myPosts' => $myPosts,
            'totalViews' => $user->posts()->sum('views'),
        ]);
    }

    /* ---------- Edit profil ---------- */

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user->id)],
            'old_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', Password::min(8)],
            'banner' => ['nullable', 'image', 'max:4096'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ], [], ['old_password' => 'old password']);

        $user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'bio' => $data['bio'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        foreach (['avatar' => 'avatars', 'banner' => 'banners'] as $field => $folder) {
            if ($request->hasFile($field)) {
                $this->deleteUpload($user->{$field});
                $user->{$field} = $this->upload($request->file($field), $folder);
            }
        }
        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    /* ---------- Helper ---------- */

    private function validatePost(Request $request, ?Post $post = null): array
    {
        $isVideo = $request->input('type') === 'video';

        return $request->validate([
            'type' => ['required', Rule::in(['post', 'video'])],
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:50000'],
            'video_url' => [Rule::requiredIf($isVideo), 'nullable', 'url', 'regex:~(youtu\.be/|youtube\.com/)~'],
            // Gambar wajib untuk berita baru biasa; video memakai thumbnail YouTube bila tidak ada gambar
            'image' => [Rule::requiredIf(! $isVideo && ! $post?->image), 'nullable', 'image', 'max:2048'],
        ], [
            'video_url.regex' => 'Link video harus dari YouTube.',
        ], [
            'category_id' => 'category',
            'content' => 'explanation',
            'video_url' => 'link video',
        ]);
    }

    /** Koordinat grafik garis (SVG 1062 x 292) dari jumlah views per bulan. */
    private function buildChart(array $views): array
    {
        $max = max(max($views), 1) * 1.15;
        $points = [];
        $i = 0;
        foreach ($views as $label => $value) {
            $points[] = [
                'label' => $label,
                'value' => $value,
                'x' => 47 + $i * 139,
                'y' => round(257 - ($value / $max) * 247, 1),
            ];
            $i++;
        }

        $path = 'M' . $points[0]['x'] . ',' . $points[0]['y'];
        for ($k = 0; $k < count($points) - 1; $k++) {
            $p0 = $points[max($k - 1, 0)];
            $p1 = $points[$k];
            $p2 = $points[$k + 1];
            $p3 = $points[min($k + 2, count($points) - 1)];
            $path .= sprintf(
                ' C%.1f,%.1f %.1f,%.1f %.1f,%.1f',
                $p1['x'] + ($p2['x'] - $p0['x']) / 6, $p1['y'] + ($p2['y'] - $p0['y']) / 6,
                $p2['x'] - ($p3['x'] - $p1['x']) / 6, $p2['y'] - ($p3['y'] - $p1['y']) / 6,
                $p2['x'], $p2['y']
            );
        }

        return ['points' => $points, 'path' => $path];
    }
}
