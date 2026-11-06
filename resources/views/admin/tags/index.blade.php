@extends('admin.layouts.app')

@section('title', 'Tag')
@section('breadcrumb')<li class="breadcrumb-item active">Tag</li>@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <form action="{{ $editing ? route('admin.tags.update', $editing) : route('admin.tags.store') }}" method="POST" class="card card-outline card-danger">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="card-header"><h3 class="card-title">{{ $editing ? 'Edit tag' : 'Tambah tag' }}</h3></div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label for="name">Nama tag</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">#</span></div>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror @error('slug') is-invalid @enderror" value="{{ old('name', $editing?->name) }}" maxlength="50" required autofocus>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex">
                    @if ($editing)
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-default">Batal</a>
                    @endif
                    <button type="submit" class="btn btn-danger ml-auto"><i class="fas fa-floppy-disk"></i> Simpan</button>
                </div>
            </form>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <form method="GET" class="form-inline">
                        <div class="input-group input-group-sm mr-2">
                            <input type="search" name="q" class="form-control" placeholder="Cari tag..." value="{{ request('q') }}" aria-label="Cari tag">
                            <div class="input-group-append"><button class="btn btn-default" type="submit" aria-label="Cari"><i class="fas fa-search"></i></button></div>
                        </div>
                        <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Urutan">
                            <option value="">A - Z</option>
                            <option value="usage" @selected(request('sort') === 'usage')>Paling dipakai</option>
                        </select>
                    </form>
                    <form action="{{ route('admin.tags.prune') }}" method="POST" class="ml-auto" data-confirm="Hapus semua tag yang tidak dipakai berita mana pun?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-broom"></i> Hapus tag tidak terpakai</button>
                    </form>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-valign-middle mb-0">
                        <thead><tr><th>Nama</th><th>Slug</th><th class="text-right">Dipakai</th><th class="text-right" style="width: 120px">Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($tags as $tag)
                                <tr class="{{ $editing?->is($tag) ? 'table-warning' : '' }}">
                                    <td>#{{ $tag->name }}</td>
                                    <td><code>{{ $tag->slug }}</code></td>
                                    <td class="text-right">
                                        @if ($tag->posts_count)
                                            <a href="{{ route('news.index', ['q' => $tag->name]) }}" target="_blank">{{ $tag->posts_count }} berita</a>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a href="{{ route('admin.tags.index', array_merge(request()->query(), ['edit' => $tag->id])) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                        @include('admin.partials.delete-button', ['action' => route('admin.tags.destroy', $tag), 'message' => "Hapus tag \"#{$tag->name}\"? Tag akan dilepas dari {$tag->posts_count} berita."])
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">Belum ada tag.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tags->hasPages())
                    <div class="card-footer clearfix">{{ $tags->links('pagination::bootstrap-4') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
