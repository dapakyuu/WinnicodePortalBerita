<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $categories = Category::withCount('posts')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'allCategories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $category = new Category($data);
        $category->image = $request->hasFile('image') ? $this->upload($request->file('image'), 'categories') : null;
        $category->save();

        return redirect()->route('admin.categories.index')->with('status', "Kategori \"{$category->name}\" berhasil dibuat.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        if ($request->hasFile('image')) {
            $this->deleteUpload($category->image);
            $category->image = $this->upload($request->file('image'), 'categories');
        }
        $category->fill($data)->save();

        return redirect()->route('admin.categories.index')->with('status', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    /** Kategori yang masih punya berita harus dipindahkan dulu ke kategori lain. */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            $target = $request->validate([
                'move_to' => ['required', 'exists:categories,id', Rule::notIn([$category->id])],
            ], [
                'move_to.required' => "Kategori \"{$category->name}\" masih memiliki berita. Pilih kategori tujuan untuk memindahkan beritanya.",
            ])['move_to'];
            $category->posts()->update(['category_id' => $target]);
        }

        $this->deleteUpload($category->image);
        $category->delete();

        return back()->with('status', "Kategori \"{$category->name}\" berhasil dihapus.");
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'slug' => ['required', 'max:60', Rule::unique('categories')->ignore($category?->id)],
            'image' => [Rule::requiredIf(! $category?->image), 'nullable', 'image', 'max:2048'],
        ], [], ['name' => 'nama', 'image' => 'gambar']);
    }
}
