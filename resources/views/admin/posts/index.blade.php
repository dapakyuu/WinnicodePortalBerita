@extends('admin.layouts.app')

@section('title', 'Berita')
@section('breadcrumb')<li class="breadcrumb-item active">Berita</li>@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="GET" class="form-row align-items-center filter-bar">
                <div class="col-md-3 mb-2 mb-md-0">
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari judul..." value="{{ request('q') }}" aria-label="Cari judul">
                </div>
                <div class="col-md-2 col-6 mb-2 mb-md-0">
                    <select name="category" class="form-control form-control-sm" aria-label="Kategori">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6 mb-2 mb-md-0">
                    <select name="author" class="form-control form-control-sm" aria-label="Penulis">
                        <option value="">Semua penulis</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}" @selected(request('author') == $author->id)>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6 mb-2 mb-md-0">
                    <select name="status" class="form-control form-control-sm" aria-label="Status">
                        <option value="">Semua status</option>
                        @foreach (['published' => 'Terbit', 'scheduled' => 'Terjadwal', 'draft' => 'Draft', 'featured' => 'Pilihan (slider)'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-6 mb-2 mb-md-0">
                    <select name="sort" class="form-control form-control-sm" aria-label="Urutan">
                        <option value="">Terbaru</option>
                        <option value="views" @selected(request('sort') === 'views')>Views</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button class="btn btn-sm btn-default mr-1" type="submit"><i class="fas fa-filter"></i> Filter</button>
                    @if (request()->hasAny(['q', 'category', 'author', 'status', 'sort']))
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <div>
                    <form id="bulkForm" action="{{ route('admin.posts.bulk-destroy') }}" method="POST" class="d-inline" data-confirm="Hapus semua berita yang dipilih?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bulk-button disabled><i class="fas fa-trash-can"></i> Hapus terpilih (<span data-bulk-count>0</span>)</button>
                    </form>
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-muted text-sm mr-3">{{ $posts->total() }} berita</span>
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-sm btn-danger"><i class="fas fa-plus"></i> Tulis Berita</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-valign-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 36px"><input type="checkbox" data-check-all aria-label="Pilih semua"></th>
                            <th>Berita</th>
                            <th>Kategori</th>
                            <th>Penulis</th>
                            <th>Status</th>
                            <th class="text-right">Views</th>
                            <th class="text-right">Komentar</th>
                            <th class="text-right" style="width: 170px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $post->id }}" form="bulkForm" data-check-item aria-label="Pilih berita"></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $post->image_url }}" alt="" class="table-thumb mr-2" loading="lazy">
                                        <div class="text-truncate" style="max-width: 340px">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-weight-bold" title="{{ $post->title }}">{{ $post->title }}</a>
                                            <div class="text-muted text-sm">
                                                @if ($post->youtubeId())<i class="fab fa-youtube text-danger"></i>@endif
                                                {{ ($post->published_at ?? $post->created_at)->format('d M Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $post->category?->name }}</td>
                                <td>{{ $post->user?->name }}</td>
                                <td>@include('admin.posts.status', ['post' => $post])</td>
                                <td class="text-right">{{ number_format($post->views) }}</td>
                                <td class="text-right">{{ $post->comments_count }}</td>
                                <td class="text-right text-nowrap">
                                    <form action="{{ route('admin.posts.featured', $post) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $post->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" title="{{ $post->is_featured ? 'Hapus dari slider beranda' : 'Tampilkan di slider beranda' }}">
                                            <i class="{{ $post->is_featured ? 'fas' : 'far' }} fa-star"></i>
                                        </button>
                                    </form>
                                    @if ($post->published_at && ! $post->published_at->isFuture())
                                        <a href="{{ route('news.show', $post) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Lihat"><i class="fas fa-eye"></i></a>
                                    @endif
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                    @include('admin.partials.delete-button', ['action' => route('admin.posts.destroy', $post), 'message' => "Hapus berita \"{$post->title}\"? Komentar, rating, dan statistiknya ikut terhapus."])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">Tidak ada berita yang cocok dengan filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($posts->hasPages())
            <div class="card-footer clearfix">{{ $posts->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
@endsection
