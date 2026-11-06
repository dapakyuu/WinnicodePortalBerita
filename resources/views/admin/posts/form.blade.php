@extends('admin.layouts.app')

@php
    $editing = $post->exists;
    $status = old('status', $editing && ! $post->published_at ? 'draft' : 'published');
@endphp

@section('title', $editing ? 'Edit Berita' : 'Tulis Berita')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Berita</a></li>
    <li class="breadcrumb-item active">{{ $editing ? 'Edit' : 'Baru' }}</li>
@endsection

@section('content')
    <form action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-outline card-danger">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Judul <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" maxlength="150" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($editing)
                                <small class="form-text text-muted">URL: {{ route('news.show', $post) }}</small>
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <label for="content">Isi berita <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-sm mb-2 d-flex flex-wrap" role="toolbar" aria-label="Format teks">
                                <button type="button" class="btn btn-default" data-format="heading" title="Subjudul"><i class="fas fa-heading"></i></button>
                                <button type="button" class="btn btn-default" data-format="bold" title="Tebal"><i class="fas fa-bold"></i></button>
                                <button type="button" class="btn btn-default" data-format="link" title="Link"><i class="fas fa-link"></i></button>
                                <button type="button" class="btn btn-default" data-format="image" title="Gambar dari URL"><i class="far fa-image"></i></button>
                                <button type="button" class="btn btn-default" data-format="color" title="Warna teks"><i class="fas fa-palette"></i></button>
                                <button type="button" class="btn btn-default" data-format="center" title="Rata tengah"><i class="fas fa-align-center"></i></button>
                            </div>
                            <textarea id="content" name="content" rows="18" class="form-control text-monospace-soft @error('content') is-invalid @enderror" required>{{ old('content', $post->content) }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">
                                Pisahkan paragraf dengan baris kosong. Format: <code>## Subjudul</code>, <code>**tebal**</code>,
                                <code>[teks](https://link)</code>, <code>![alt](https://gambar.jpg)</code>,
                                <code>[color=#f81539]teks[/color]</code>, <code>[center]teks[/center]</code>.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Publikasi</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Status</label>
                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                <label class="btn btn-outline-success flex-fill {{ $status === 'published' ? 'active' : '' }}">
                                    <input type="radio" name="status" value="published" @checked($status === 'published')> Terbit
                                </label>
                                <label class="btn btn-outline-secondary flex-fill {{ $status === 'draft' ? 'active' : '' }}">
                                    <input type="radio" name="status" value="draft" @checked($status === 'draft')> Draft
                                </label>
                            </div>
                        </div>
                        <div class="form-group" data-publish-date>
                            <label for="published_at">Tanggal terbit</label>
                            <input type="datetime-local" id="published_at" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', ($post->published_at ?? now())->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">Isi tanggal mendatang untuk menjadwalkan berita.</small>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured))>
                            <label class="custom-control-label" for="is_featured">Tampilkan di slider beranda</label>
                        </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-danger ml-auto"><i class="fas fa-floppy-disk"></i> {{ $editing ? 'Simpan perubahan' : 'Simpan berita' }}</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Detail</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="category_id">Kategori <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">Penulis <span class="text-danger">*</span></label>
                            <select id="user_id" name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $post->user_id) == $user->id)>{{ $user->name }} ({{ '@' . $user->username }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tags">Tag</label>
                            <input type="text" id="tags" name="tags" class="form-control @error('tags') is-invalid @enderror" value="{{ old('tags', $post->tags?->pluck('name')->implode(', ')) }}" placeholder="sport, travel, croatia">
                            <small class="form-text text-muted">Pisahkan dengan koma. Tag baru dibuat otomatis.</small>
                        </div>
                        <div class="form-group mb-0">
                            <label for="views">Views</label>
                            <input type="number" id="views" name="views" min="0" class="form-control @error('views') is-invalid @enderror" value="{{ old('views', $post->views ?? 0) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Media</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="image">Gambar utama</label>
                            <img src="{{ $post->image ? $post->image_url : '' }}" alt="" class="img-fluid rounded mb-2 image-preview {{ $post->image ? '' : 'd-none' }}" data-preview-for="image">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Pilih gambar...</label>
                            </div>
                            <small class="form-text text-muted">JPG/PNG/WebP, maks 2 MB. Tanpa gambar, berita video memakai thumbnail YouTube.</small>
                            @if ($editing && $post->image)
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                    <label class="custom-control-label" for="remove_image">Hapus gambar saat ini</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-group mb-0">
                            <label for="video_url">Link video YouTube</label>
                            <input type="url" id="video_url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $post->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                            @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
