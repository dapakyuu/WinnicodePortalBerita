<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $users = User::withCount(['posts', 'comments'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('username', 'like', '%' . $request->q . '%')
                ->orWhere('email', 'like', '%' . $request->q . '%')))
            ->when(in_array($request->role, ['admin', 'user'], true), fn ($q) => $q->where('role', $request->role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = new User($data);
        $user->role = $data['role'];
        $this->applyUploads($request, $user);
        $user->save();

        return redirect()->route('admin.users.index')->with('status', "User \"{$user->username}\" berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        // Admin tidak bisa menurunkan role dirinya sendiri (agar tidak terkunci dari dashboard)
        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->withInput()->withErrors(['role' => 'Kamu tidak bisa menghapus role admin dari akunmu sendiri.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->fill($data);
        $user->role = $data['role'];
        $this->applyUploads($request, $user);
        $user->save();

        return redirect()->route('admin.users.index')->with('status', "User \"{$user->username}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $this->deleteUpload($user->avatar);
        $this->deleteUpload($user->banner);
        foreach ($user->posts as $post) {
            $this->deleteUpload($post->image);
        }
        $user->delete(); // berita, komentar, rating, dan bookmark user ikut terhapus

        return back()->with('status', "User \"{$user->username}\" beserta datanya berhasil dihapus.");
    }

    private function applyUploads(Request $request, User $user): void
    {
        foreach (['avatar' => 'avatars', 'banner' => 'banners'] as $field => $folder) {
            if ($request->hasFile($field)) {
                $this->deleteUpload($user->{$field});
                $user->{$field} = $this->upload($request->file($field), $folder);
            }
        }
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ], [], ['name' => 'nama']);
    }
}
