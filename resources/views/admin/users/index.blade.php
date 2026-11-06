@extends('admin.layouts.app')

@section('title', 'User')
@section('breadcrumb')<li class="breadcrumb-item active">User</li>@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap">
            <form method="GET" class="form-inline">
                <div class="input-group input-group-sm mr-2">
                    <input type="search" name="q" class="form-control" placeholder="Nama, username, email..." value="{{ request('q') }}" aria-label="Cari user">
                    <div class="input-group-append"><button class="btn btn-default" type="submit" aria-label="Cari"><i class="fas fa-search"></i></button></div>
                </div>
                <select name="role" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Role">
                    <option value="">Semua role</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="user" @selected(request('role') === 'user')>User</option>
                </select>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-danger ml-auto"><i class="fas fa-user-plus"></i> Tambah User</a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-valign-middle mb-0">
                <thead>
                    <tr><th>User</th><th>Email</th><th>Role</th><th class="text-right">Berita</th><th class="text-right">Komentar</th><th>Bergabung</th><th class="text-right" style="width: 120px">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}" alt="" class="img-circle img-size-32 mr-2">
                                    <div>
                                        <div class="font-weight-bold">{{ $user->name }} @if ($user->is(auth()->user()))<span class="badge badge-light">Kamu</span>@endif</div>
                                        <small class="text-muted">{{ '@' . $user->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge {{ $user->isAdmin() ? 'badge-danger' : 'badge-secondary' }}">{{ ucfirst($user->role) }}</span></td>
                            <td class="text-right"><a href="{{ route('admin.posts.index', ['author' => $user->id]) }}">{{ $user->posts_count }}</a></td>
                            <td class="text-right">{{ $user->comments_count }}</td>
                            <td><small>{{ $user->created_at->format('d M Y') }}</small></td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                @unless ($user->is(auth()->user()))
                                    @include('admin.partials.delete-button', ['action' => route('admin.users.destroy', $user), 'message' => "Hapus user \"{$user->username}\"? {$user->posts_count} berita dan {$user->comments_count} komentarnya ikut terhapus."])
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer clearfix">{{ $users->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
@endsection
