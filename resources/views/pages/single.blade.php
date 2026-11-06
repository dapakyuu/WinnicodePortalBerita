@extends('layouts.app')

@section('title', $post->title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/single.css') }}">
@endpush

@php
    $ratingLabels = [1 => 'Very Bad', 2 => 'Bad', 3 => 'Okay', 4 => 'Good', 5 => 'Excellent'];
    $ratingIcons = [1 => 'fa-face-angry', 2 => 'fa-face-frown', 3 => 'fa-face-meh', 4 => 'fa-face-smile', 5 => 'fa-face-laugh-beam'];
@endphp

@section('content')
    <div class="container single">
        <div class="single__main">
            {{-- ===== Konten artikel ===== --}}
            <article class="article">
                <header class="article__top">
                    <h1 class="article__title">{{ $post->title }}</h1>
                    @if ($post->youtubeId())
                        <div class="article__video">
                            <iframe src="https://www.youtube-nocookie.com/embed/{{ $post->youtubeId() }}" title="{{ $post->title }}"
                                    allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                        </div>
                    @else
                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="article__image">
                    @endif
                </header>

                <ul class="article__meta">
                    <li><i class="fa-regular fa-calendar"></i> {{ $post->date_label }}</li>
                    <li><a href="#comments"><i class="fa-regular fa-comment"></i> {{ $post->comments_count }} Comments</a></li>
                    <li><a href="{{ route('news.category', $post->category) }}"><i class="fa-regular fa-folder"></i> {{ $post->category->name }}</a></li>
                    <li><i class="fa-regular fa-eye"></i> {{ number_format($post->views) }} Views</li>
                    @if ($post->ratings_count)
                        <li><i class="fa-regular fa-star"></i> {{ number_format($post->ratings_avg_rating, 1) }} / 5</li>
                    @endif
                </ul>

                <div class="article__body">
                    {!! $post->content_html !!}
                </div>

                @if ($post->canBeManagedBy(auth()->user()))
                    <div class="article__owner">
                        <a href="{{ route('profile.posts.edit', $post) }}" class="chip-btn"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                        <form action="{{ route('profile.posts.destroy', $post) }}" method="POST" data-confirm="Hapus berita ini?">
                            @csrf @method('DELETE')
                            <button type="submit" class="chip-btn"><i class="fa-regular fa-trash-can"></i> Delete</button>
                        </form>
                    </div>
                @endif
            </article>

            {{-- ===== Komentar ===== --}}
            <section class="comments" id="comments" aria-label="Komentar">
                @include('partials.section-title', ['title' => 'Comments (' . $post->comments_count . ')'])
                <div class="comments__list">
                    @forelse ($comments as $comment)
                        <div class="comment" id="comment-{{ $comment->id }}">
                            @include('partials.comment-head', ['comment' => $comment, 'canReply' => true])
                            <p class="comment__text">{{ $comment->body }}</p>

                            @if ($comment->replies->isNotEmpty())
                                <div class="comment__replies">
                                    @foreach ($comment->replies as $reply)
                                        <div class="comment comment--reply" id="comment-{{ $reply->id }}">
                                            @include('partials.comment-head', ['comment' => $reply, 'canReply' => false])
                                            <p class="comment__text">{{ $reply->body }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="empty-state">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                    @endforelse
                </div>
            </section>

            {{-- ===== Form komentar ===== --}}
            <section class="add-comment" id="add-comment" aria-label="Tambah komentar">
                @include('partials.section-title', ['title' => 'Add A Comment'])
                @auth
                    <form action="{{ route('news.comment', $post) }}" method="POST" class="add-comment__form" data-comment-form>
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ old('parent_id') }}" data-reply-input>
                        <div class="add-comment__user">
                            <img src="{{ auth()->user()->avatar_url }}" alt="" width="58" height="58">
                            <span>{{ auth()->user()->name }}</span>
                        </div>

                        <div class="add-comment__replying" data-reply-info hidden>
                            <i class="fa-solid fa-reply"></i> Membalas <strong data-reply-name></strong>
                            <button type="button" data-reply-cancel aria-label="Batal membalas"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <label for="comment" class="add-comment__label">Comment</label>
                        <textarea id="comment" name="comment" class="add-comment__textarea @error('comment') is-invalid @enderror" placeholder="Write your comment here..." maxlength="1000" required>{{ old('comment') }}</textarea>
                        @error('comment') <p class="field__error">{{ $message }}</p> @enderror

                        <div class="add-comment__actions">
                            <div class="rate">
                                <span class="rate__title">Rate The Usefulness Of The Article</span>
                                <div class="rate__items" role="radiogroup" aria-label="Penilaian artikel">
                                    @php($selected = (int) old('rating', $userRating))
                                    @foreach ([1, 2, 3, 4] as $value)
                                        <button type="button" class="rate__icon {{ $selected === $value ? 'is-active' : '' }}" data-rate="{{ $value }}" aria-label="{{ $ratingLabels[$value] }}" title="{{ $ratingLabels[$value] }}">
                                            <i class="fa-regular {{ $ratingIcons[$value] }}"></i>
                                        </button>
                                    @endforeach
                                    <button type="button" class="rate__good {{ $selected === 5 ? 'is-active' : '' }}" data-rate="5" title="{{ $ratingLabels[5] }}">
                                        <i class="fa-regular fa-face-laugh-beam"></i> Good
                                    </button>
                                </div>
                                <input type="hidden" name="rating" value="{{ $selected ?: '' }}" data-rate-input>
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fa-regular fa-paper-plane"></i> Send Comment
                            </button>
                        </div>
                    </form>
                @else
                    <div class="login-prompt">
                        <p>Silakan login atau daftar terlebih dahulu untuk menulis komentar dan memberi rating.</p>
                        <div>
                            <a href="{{ route('login') }}" class="btn-solid">Login</a>
                            <a href="{{ route('register') }}" class="btn-soft">Register</a>
                        </div>
                    </div>
                @endauth
            </section>
        </div>

        {{-- ===== Sidebar ===== --}}
        <aside class="sidebar">
            <div class="sidebar__actions">
                <button type="button" class="chip-btn" data-share><i class="fa-solid fa-share-nodes"></i> Share</button>
                @include('partials.bookmark-button', ['post' => $post, 'class' => 'chip-btn', 'label' => true])
                @auth
                    <form action="{{ route('news.report', $post) }}" method="POST" data-report-form>
                        @csrf
                        <input type="hidden" name="reason" data-report-reason>
                        <button type="submit" class="chip-btn"><i class="fa-regular fa-flag"></i> Report</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="chip-btn"><i class="fa-regular fa-flag"></i> Report</a>
                @endauth
            </div>

            <div class="author-card">
                <img src="{{ $post->user->avatar_url }}" alt="" width="87" height="87">
                <span class="author-card__name">{{ $post->user->name }}</span>
                <span class="author-card__role">{{ $authorPostCount }} Posts</span>
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="sidebar__tags">
                    @include('partials.section-title', ['title' => 'Tags'])
                    <div class="tag-list">
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('news.index', ['q' => $tag->name]) }}" class="tag-chip">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="top-post">
                @include('partials.section-title', ['title' => 'Top Post'])
                <div class="top-post__list">
                    @foreach ($topPosts as $top)
                        <a href="{{ route('news.show', $top) }}" class="mini-post">
                            <img src="{{ $top->image_url }}" alt="" loading="lazy">
                            <div class="mini-post__body">
                                <h3 class="mini-post__title">{{ $top->title }}</h3>
                                <span class="mini-post__date">{{ $top->date_label }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Slot iklan --}}
            <a href="{{ route('about') }}#contact" class="ad-banner" style="background-image: url('{{ asset('images/tags/abstract.png') }}')">
                <span class="ad-banner__title">Advertising</span>
                <span class="ad-banner__subtitle">Your advertisement could be here</span>
            </a>
            <a href="{{ route('about') }}#contact" class="ad-banner" style="background-image: url('{{ asset('images/posts/technology-04.png') }}')">
                <span class="ad-banner__title">Advertising</span>
                <span class="ad-banner__subtitle">Contact us to place your ads</span>
            </a>
        </aside>
    </div>

    {{-- ===== Related posts ===== --}}
    <section class="container section related-posts" data-carousel>
        <div class="section-header">
            @include('partials.section-title', ['title' => 'Related Posts'])
            <div class="section-header__arrows">
                <button type="button" class="arrow-btn" aria-label="Sebelumnya" data-carousel-prev><i class="fa-solid fa-angle-left"></i></button>
                <button type="button" class="arrow-btn" aria-label="Berikutnya" data-carousel-next><i class="fa-solid fa-angle-right"></i></button>
            </div>
        </div>
        <div class="carousel" data-carousel-track>
            @foreach ($related as $item)
                @include('partials.post-card', ['post' => $item])
            @endforeach
        </div>
    </section>
@endsection
