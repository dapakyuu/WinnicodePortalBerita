@extends('layouts.app')

@php
    $editing = $post->exists;
    $type = old('type', $post->video_url ? 'video' : 'post');
@endphp

@section('title', $editing ? 'Edit Post' : 'Profile - Send Post')
@section('body_class', 'page-gradient')

@section('content')
    @include('partials.profile-top', ['user' => $user, 'active' => 'send'])

    <section class="container profile-content">
        <div class="tabs" role="tablist" data-post-type-tabs>
            <button type="button" class="tabs__item {{ $type === 'post' ? 'is-active' : '' }}" role="tab" data-post-type="post">Send Post</button>
            <button type="button" class="tabs__item {{ $type === 'video' ? 'is-active' : '' }}" role="tab" data-post-type="video">Send Video</button>
        </div>

        <form action="{{ $editing ? route('profile.posts.update', $post) : route('profile.store') }}" method="POST"
              enctype="multipart/form-data" class="write-form" data-write-form novalidate>
            @csrf
            @if ($editing) @method('PUT') @endif
            <input type="hidden" name="type" value="{{ $type }}" data-post-type-input>

            <div class="write-form__main">
                <div class="write-form__row">
                    @include('partials.input', ['name' => 'title', 'label' => 'Title', 'value' => $post->title, 'class' => 'field--title'])
                    <div class="field">
                        <label for="category_id" class="field__label">Category</label>
                        <select id="category_id" name="category_id" class="field__input @error('category_id') is-invalid @enderror">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $post->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="field__error">{{ $message }}</p> @enderror
                    </div>
                    <div class="field field--tags">
                        <label for="tags" class="field__label">Add Tags</label>
                        <div class="field__group">
                            <input type="text" id="tags" name="tags" value="{{ old('tags', $post->tags?->pluck('name')->implode(', ')) }}" class="field__input" placeholder="sport, travel" data-tags-input>
                            <button type="button" class="field__addon" aria-label="Tambah pemisah tag" data-tags-add><i class="fa-solid fa-plus"></i></button>
                        </div>
                        @error('tags') <p class="field__error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="field" data-video-field @if ($type !== 'video') hidden @endif>
                    <label for="video_url" class="field__label">Link Video YouTube</label>
                    <input type="url" id="video_url" name="video_url" value="{{ old('video_url', $post->video_url) }}" class="field__input @error('video_url') is-invalid @enderror" placeholder="https://www.youtube.com/watch?v=...">
                    @error('video_url') <p class="field__error">{{ $message }}</p> @enderror
                </div>

                @include('partials.editor', ['name' => 'content', 'label' => 'Explanation', 'value' => $post->content])
            </div>

            <div class="write-form__side">
                @include('partials.dropzone', ['name' => 'image', 'label' => 'Add Image', 'current' => $post->image ? $post->image_url : null])
                <div class="write-form__actions">
                    <button type="button" class="btn-soft" data-preview-open><i class="fa-regular fa-eye"></i> Preview</button>
                    <button type="submit" class="btn-solid"><i class="fa-regular fa-paper-plane"></i> {{ $editing ? 'Update' : 'Publish' }}</button>
                </div>
            </div>
        </form>
    </section>

    {{-- Pratinjau berita sebelum dipublikasikan --}}
    <dialog class="preview-dialog" data-preview-dialog>
        <div class="preview-dialog__head">
            <strong>Preview</strong>
            <button type="button" data-preview-close aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="preview-dialog__body">
            <h1 class="article__title" data-preview-title></h1>
            <img alt="" class="preview-dialog__image" data-preview-image>
            <div class="article__body" data-preview-content></div>
        </div>
    </dialog>
@endsection
