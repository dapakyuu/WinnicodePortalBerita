@extends('admin.layouts.app')

@section('title', 'Komentar')
@section('breadcrumb')<li class="breadcrumb-item active">Komentar</li>@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="GET" class="form-row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari isi komentar..." value="{{ request('q') }}" aria-label="Cari komentar">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <select name="post" class="form-control form-control-sm" aria-label="Berita">
                        <option value="">Semua berita</option>
                        @foreach ($posts as $post)
                            <option value="{{ $post->id }}" @selected(request('post') == $post->id)>{{ \Illuminate\Support\Str::limit($post->title, 60) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <select name="type" class="form-control form-control-sm" aria-label="Jenis">
                        <option value="">Semua jenis</option>
                        <option value="root" @selected(request('type') === 'root')>Komentar utama</option>
                        <option value="reply" @selected(request('type') === 'reply')>Balasan</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button class="btn btn-sm btn-default mr-1" type="submit"><i class="fas fa-filter"></i> Filter</button>
                    @if (request()->hasAny(['q', 'post', 'type']))
                        <a href="{{ route('admin.comments.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <form id="bulkForm" action="{{ route('admin.comments.bulk-destroy') }}" method="POST" data-confirm="Hapus semua komentar yang dipilih? Balasannya ikut terhapus.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" data-bulk-button disabled><i class="fas fa-trash-can"></i> Hapus terpilih (<span data-bulk-count>0</span>)</button>
                </form>
                <span class="text-muted text-sm">{{ $comments->total() }} komentar</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 36px"><input type="checkbox" data-check-all aria-label="Pilih semua"></th>
                            <th style="width: 200px">User</th>
                            <th>Komentar</th>
                            <th style="width: 240px">Berita</th>
                            <th style="width: 130px">Waktu</th>
                            <th class="text-right" style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comments as $comment)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $comment->id }}" form="bulkForm" data-check-item aria-label="Pilih komentar"></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $comment->user->avatar_url }}" alt="" class="img-circle img-size-32 mr-2">
                                        <div class="text-truncate">
                                            <div class="font-weight-bold text-truncate">{{ $comment->user->name }}</div>
                                            <small class="text-muted">{{ '@' . $comment->user->username }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($comment->parent)
                                        <small class="text-muted d-block"><i class="fas fa-reply"></i> membalas {{ $comment->parent->user?->name }}</small>
                                    @endif
                                    {{ \Illuminate\Support\Str::limit($comment->body, 200) }}
                                    @if ($comment->replies_count)
                                        <small class="d-block text-muted">{{ $comment->replies_count }} balasan</small>
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width: 240px">
                                    <a href="{{ route('news.show', $comment->post) }}#comment-{{ $comment->id }}" target="_blank" title="{{ $comment->post->title }}">{{ $comment->post->title }}</a>
                                </td>
                                <td><small title="{{ $comment->created_at->format('d M Y H:i') }}">{{ $comment->created_at->diffForHumans() }}</small></td>
                                <td class="text-right text-nowrap">
                                    <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                    @include('admin.partials.delete-button', ['action' => route('admin.comments.destroy', $comment), 'message' => 'Hapus komentar ini?' . ($comment->replies_count ? " {$comment->replies_count} balasannya ikut terhapus." : '')])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada komentar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($comments->hasPages())
            <div class="card-footer clearfix">{{ $comments->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
@endsection
