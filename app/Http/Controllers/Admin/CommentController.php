<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $comments = Comment::with(['user:id,name,username,avatar', 'post:id,title,slug', 'parent.user:id,name'])
            ->withCount('replies')
            ->when($request->filled('q'), fn ($q) => $q->where('body', 'like', '%' . $request->q . '%'))
            ->when($request->filled('post'), fn ($q) => $q->where('post_id', $request->post))
            ->when($request->type === 'root', fn ($q) => $q->whereNull('parent_id'))
            ->when($request->type === 'reply', fn ($q) => $q->whereNotNull('parent_id'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'posts' => Post::has('comments')->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function edit(Comment $comment): View
    {
        return view('admin.comments.edit', ['comment' => $comment->load(['user', 'post', 'parent.user'])]);
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $comment->update($request->validate(['body' => ['required', 'string', 'max:2000']], [], ['body' => 'komentar']));

        return redirect()->route('admin.comments.index')->with('status', 'Komentar berhasil diperbarui.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete(); // balasan ikut terhapus (cascade)

        return back()->with('status', 'Komentar berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        $count = Comment::whereIn('id', $ids)->delete();

        return back()->with('status', "{$count} komentar berhasil dihapus.");
    }
}
