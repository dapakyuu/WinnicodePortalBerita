{{-- Daftar berita: dipakai halaman New Post / Trendings / pencarian dan Category --}}
@extends('layouts.app')

@section('title', $title)
@section('body_class', 'page-gradient')

@section('content')
    @isset($category)
        <div class="container top-category">
            <nav class="top-category__filter" aria-label="Urutkan">
                @foreach (['new' => 'New', 'popular' => 'Popular', 'top' => 'Top'] as $key => $label)
                    <a href="{{ route('news.category', ['category' => $category, 'sort' => $key]) }}"
                       class="{{ $sort === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <button type="button" class="top-category__view" aria-label="Ubah tampilan grid/list" data-view-toggle>
                <i class="fa-solid fa-grip"></i>
            </button>
        </div>
    @endisset

    <section class="container news-list {{ isset($category) ? 'news-list--category' : '' }}">
        <div class="news-list__header">
            @include('partials.section-title', ['title' => $title])
            <span class="news-list__query">{{ $posts->total() }} berita</span>
        </div>

        @if ($posts->isEmpty())
            <div class="empty-box">
                <i class="fa-regular fa-newspaper"></i>
                <p>Belum ada berita yang cocok.</p>
                <a href="{{ route('news.index') }}" class="btn-solid">Lihat semua berita</a>
            </div>
        @else
            <div class="grid-4 news-list__grid" data-view-target>
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        @endif

        {{ $posts->links() }}
    </section>
@endsection
