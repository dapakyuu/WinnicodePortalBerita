@extends('admin.layouts.app')

@php $editing = $category->exists; @endphp

@section('title', $editing ? 'Edit Kategori' : 'Tambah Kategori')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kategori</a></li>
    <li class="breadcrumb-item active">{{ $editing ? 'Edit' : 'Baru' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <form action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="card card-outline card-danger">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" maxlength="50" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}" maxlength="60" placeholder="otomatis dari nama">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">Dipakai di URL: /kategori/<b>slug</b></small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="image">Gambar {!! $editing ? '' : '<span class="text-danger">*</span>' !!}</label>
                        <img src="{{ $category->image ? $category->image_url : '' }}" alt="" class="img-fluid rounded mb-2 image-preview {{ $category->image ? '' : 'd-none' }}" data-preview-for="image">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                            <label class="custom-file-label" for="image">Pilih gambar...</label>
                        </div>
                        <small class="form-text text-muted">Tampil di bilah kategori beranda. Maks 2 MB.</small>
                    </div>
                </div>
                <div class="card-footer d-flex">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-default">Batal</a>
                    <button type="submit" class="btn btn-danger ml-auto"><i class="fas fa-floppy-disk"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
