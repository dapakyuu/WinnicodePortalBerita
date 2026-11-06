<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(Request $request): View
    {
        $tags = Tag::withCount('posts')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->when($request->sort === 'usage', fn ($q) => $q->orderByDesc('posts_count'), fn ($q) => $q->orderBy('name'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.tags.index', ['tags' => $tags, 'editing' => $request->filled('edit') ? Tag::find($request->edit) : null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tag = Tag::create($this->validated($request));

        return redirect()->route('admin.tags.index')->with('status', "Tag \"{$tag->name}\" berhasil dibuat.");
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $tag->update($this->validated($request, $tag));

        return redirect()->route('admin.tags.index')->with('status', "Tag \"{$tag->name}\" berhasil diperbarui.");
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete(); // relasi post_tag ikut terhapus (cascade)

        return back()->with('status', "Tag \"{$tag->name}\" berhasil dihapus.");
    }

    /** Hapus semua tag yang tidak dipakai berita mana pun. */
    public function prune(): RedirectResponse
    {
        $count = Tag::doesntHave('posts')->delete();

        return back()->with('status', "{$count} tag yang tidak terpakai berhasil dihapus.");
    }

    private function validated(Request $request, ?Tag $tag = null): array
    {
        $request->merge([
            'name' => trim(ltrim(trim((string) $request->input('name')), '#')),
            'slug' => Str::slug(ltrim((string) $request->input('name'), '#')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'slug' => ['required', Rule::unique('tags')->ignore($tag?->id)],
        ], ['slug.unique' => 'Tag dengan nama ini sudah ada.', 'slug.required' => 'Nama tag tidak valid.'], ['name' => 'nama tag']);
    }
}
