@extends('layouts.app')

@section('title', 'Profile - Posts')
@section('body_class', 'page-gradient')

@php
    $moods = [
        'none' => ['fa-face-meh-blank', 'rgba(62,50,50,.3)'],
        'bad' => ['fa-face-frown', '#f81539'],
        'ok' => ['fa-face-meh', '#f5b014'],
        'good' => ['fa-face-smile', '#1c9bfb'],
        'great' => ['fa-face-laugh-beam', '#2ec05a'],
    ];
    $last = end($chart['points']);
@endphp

@section('content')
    @include('partials.profile-top', ['user' => $user, 'active' => 'posts'])

    <section class="container profile-content stats">
        <div class="stats__views">
            @include('partials.section-title', ['title' => 'View Posts'])
            <div class="chart-card">
                <svg class="chart" viewBox="0 0 1062 292" role="img" aria-label="Grafik jumlah pembaca per bulan">
                    @foreach ($chart['points'] as $point)
                        <rect x="{{ $point['x'] - 7 }}" y="0" width="14" height="257" rx="7" class="chart__bar" />
                        <text x="{{ $point['x'] }}" y="287" class="chart__label">{{ $point['label'] }}</text>
                    @endforeach
                    <path d="{{ $chart['path'] }}" class="chart__line" />
                    @foreach ($chart['points'] as $point)
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="6" class="chart__dot">
                            <title>{{ $point['label'] }}: {{ number_format($point['value']) }} views</title>
                        </circle>
                    @endforeach
                    <text x="{{ $last['x'] }}" y="{{ max($last['y'] - 16, 10) }}" class="chart__value">{{ number_format($last['value']) }}</text>
                </svg>
            </div>
        </div>

        <div class="stats__satisfaction">
            @include('partials.section-title', ['title' => 'Satisfaction Of Posts'])
            <div class="satisfaction-card">
                @foreach ($satisfaction as $item)
                    <div class="satisfaction-item" title="{{ $item['mood'] === 'none' ? 'Belum ada rating' : 'Rata-rata rating x 6' }}">
                        <span class="satisfaction-item__month">{{ $item['month'] }}</span>
                        <i class="fa-regular {{ $moods[$item['mood']][0] }}" style="color: {{ $moods[$item['mood']][1] }}"></i>
                        <span class="satisfaction-item__points">{{ $item['points'] }} Points</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="container your-posts">
        <div class="news-list__header">
            @include('partials.section-title', ['title' => 'Your Posts'])
            <span class="news-list__query">{{ $myPosts->total() }} berita · {{ number_format($totalViews) }} total views</span>
        </div>

        @if ($myPosts->isEmpty())
            <div class="empty-box">
                <i class="fa-regular fa-newspaper"></i>
                <p>Kamu belum menulis berita.</p>
                <a href="{{ route('profile.create') }}" class="btn-solid">Tulis berita pertama</a>
            </div>
        @else
            <div class="grid-4">
                @foreach ($myPosts as $post)
                    <article class="post-card post-card--mine">
                        <a href="{{ route('news.show', $post) }}" class="post-card__image">
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy">
                        </a>
                        <h3 class="post-card__title"><a href="{{ route('news.show', $post) }}" title="{{ $post->title }}">{{ $post->title }}</a></h3>
                        <div class="post-card__stats">
                            <span><i class="fa-regular fa-eye"></i> {{ short_number($post->views) }}</span>
                            <a href="{{ route('profile.posts.edit', $post) }}" aria-label="Edit berita"><i class="fa-regular fa-pen-to-square"></i></a>
                            <form action="{{ route('profile.posts.destroy', $post) }}" method="POST" data-confirm="Hapus berita “{{ $post->title }}”?">
                                @csrf @method('DELETE')
                                <button type="submit" aria-label="Hapus berita"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $myPosts->links() }}
        @endif
    </section>
@endsection
