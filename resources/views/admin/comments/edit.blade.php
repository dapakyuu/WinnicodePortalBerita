@extends('admin.layouts.app')

@section('title', 'Edit Komentar')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.comments.index') }}">Komentar</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <form action="{{ route('admin.comments.update', $comment) }}" method="POST" class="card card-outline card-danger">
                @csrf @method('PUT')
                <div class="card-body">
                    <dl class="row mb-3">
                        <dt class="col-sm-3">User</dt>
                        <dd class="col-sm-9">{{ $comment->user->name }} <span class="text-muted">({{ '@' . $comment->user->username }})</span></dd>
                        <dt class="col-sm-3">Berita</dt>
                        <dd class="col-sm-9"><a href="{{ route('news.show', $comment->post) }}#comment-{{ $comment->id }}" target="_blank">{{ $comment->post->title }}</a></dd>
                        @if ($comment->parent)
                            <dt class="col-sm-3">Membalas</dt>
                            <dd class="col-sm-9"><span class="text-muted">{{ $comment->parent->user?->name }}:</span> {{ \Illuminate\Support\Str::limit($comment->parent->body, 120) }}</dd>
                        @endif
                        <dt class="col-sm-3">Dikirim</dt>
                        <dd class="col-sm-9">{{ $comment->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                    <div class="form-group mb-0">
                        <label for="body">Komentar</label>
                        <textarea id="body" name="body" rows="6" maxlength="2000" class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $comment->body) }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer d-flex">
                    <a href="{{ route('admin.comments.index') }}" class="btn btn-default">Batal</a>
                    <button type="submit" class="btn btn-danger ml-auto"><i class="fas fa-floppy-disk"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
