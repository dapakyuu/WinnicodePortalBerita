<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $posts = Post::with(['user:id,name,username', 'category:id,name,slug'])
            ->withCount('comments')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->filled('author'), fn ($q) => $q->where('user_id', $request->author))
            ->when($request->status === 'published', fn ($q) => $q->published())
            ->when($request->status === 'draft', fn ($q) => $q->whereNull('published_at'))
            ->when($request->status === 'scheduled', fn ($q) => $q->where('published_at', '>', now()))
            ->when($request->status === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when($request->sort === 'views', fn ($q) => $q->orderByDesc('views'), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'authors' => User::has('posts')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', $this->formData(new Post(['published_at' => now(), 'user_id' => auth()->id()])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $post = new Post($data);
        $post->slug = Post::uniqueSlug($data['title']);
        $post->image = $request->hasFile('image') ? $this->upload($request->file('image'), 'posts') : null;
        $post->save();
        $post->tags()->sync(Tag::idsFromString($request->input('tags')));

        return redirect()->route('admin.posts.index')->with('status', "Berita \"{$post->title}\" berhasil dibuat.");
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', $this->formData($post->load('tags')));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request, $post);

        if ($request->boolean('remove_image') && ! $request->hasFile('image')) {
            $this->deleteUpload($post->image);
            $post->image = null;
        }
        if ($request->hasFile('image')) {
            $this->deleteUpload($post->image);
            $post->image = $this->upload($request->file('image'), 'posts');
        }
        if ($post->title !== $data['title']) {
            $post->slug = Post::uniqueSlug($data['title'], $post->id);
        }
        $post->fill($data)->save();
        $post->tags()->sync(Tag::idsFromString($request->input('tags')));

        return redirect()->route('admin.posts.index')->with('status', "Berita \"{$post->title}\" berhasil diperbarui.");
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->deleteUpload($post->image);
        $post->delete();

        return back()->with('status', "Berita \"{$post->title}\" berhasil dihapus.");
    }

    /** Jadikan / batalkan sebagai berita pilihan (slider beranda). */
    public function toggleFeatured(Post $post): RedirectResponse
    {
        $post->update(['is_featured' => ! $post->is_featured]);

        return back()->with('status', $post->is_featured
            ? "\"{$post->title}\" ditampilkan di slider beranda."
            : "\"{$post->title}\" dihapus dari slider beranda.");
    }

    /** Hapus beberapa berita sekaligus. */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        $posts = Post::whereIn('id', $ids)->get();
        $posts->each(function (Post $post) {
            $this->deleteUpload($post->image);
            $post->delete();
        });

        return back()->with('status', "{$posts->count()} berita berhasil dihapus.");
    }

    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name', 'username']),
        ];
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'user_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string', 'max:50000'],
            'tags' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'regex:~(youtu\.be/|youtube\.com/)~'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'published_at' => ['nullable', 'date'],
            'views' => ['nullable', 'integer', 'min:0'],
        ], [
            'video_url.regex' => 'Link video harus dari YouTube.',
        ], [
            'title' => 'judul', 'category_id' => 'kategori', 'user_id' => 'penulis',
            'content' => 'isi berita', 'video_url' => 'link video', 'image' => 'gambar',
        ]);

        return [
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'user_id' => $data['user_id'],
            'content' => $data['content'],
            'video_url' => $data['video_url'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'views' => $data['views'] ?? $post?->views ?? 0,
            'published_at' => $data['status'] === 'draft' ? null : ($data['published_at'] ?? now()),
        ];
    }
}
