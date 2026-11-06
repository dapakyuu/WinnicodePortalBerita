@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    {{-- ===== Hero: tags, slider, popular posts (latar gradasi pink) ===== --}}
    <div class="hero-bg">
        <section class="container top-tags" aria-label="Kategori populer">
            <div class="top-tags__track" data-tags-track>
                @foreach ($tags as $tag)
                    <a href="{{ route('news.category', $tag) }}" class="tag">
                        <span class="tag__bg" style="background-image: url('{{ $tag->image_url }}')"></span>
                        <span class="tag__label">#{{ $tag->name }}</span>
                    </a>
                @endforeach
            </div>
            <div class="top-tags__fade">
                <button type="button" class="arrow-btn" aria-label="Geser kategori" data-tags-next>
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>
        </section>

        @if ($slides->isNotEmpty())
            <section class="container slider" data-slider aria-label="Berita utama">
                @foreach ($slides as $i => $slide)
                    <div class="slider__slide {{ $i === 0 ? 'is-active' : '' }}" data-slide>
                        <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" class="slider__image">
                        <a href="{{ route('news.show', $slide) }}" class="slider__content">
                            <h2 class="slider__title">{{ $slide->title }}</h2>
                            <p class="slider__excerpt">{{ $slide->excerpt }}</p>
                        </a>
                    </div>
                @endforeach
                <div class="slider__dots" role="tablist">
                    @foreach ($slides as $i => $slide)
                        <button type="button" class="slider__dot {{ $i === 0 ? 'is-active' : '' }}" data-slider-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
                    @endforeach
                </div>
                <button type="button" class="arrow-btn slider__prev" aria-label="Sebelumnya" data-slider-prev>
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button type="button" class="arrow-btn slider__next" aria-label="Berikutnya" data-slider-next>
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </section>
        @endif

        <section class="container section popular-posts" data-carousel>
            <div class="section-header">
                @include('partials.section-title', ['title' => 'Popular Posts'])
                <div class="section-header__arrows">
                    <button type="button" class="arrow-btn" aria-label="Sebelumnya" data-carousel-prev><i class="fa-solid fa-angle-left"></i></button>
                    <button type="button" class="arrow-btn" aria-label="Berikutnya" data-carousel-next><i class="fa-solid fa-angle-right"></i></button>
                </div>
            </div>
            <div class="carousel" data-carousel-track>
                @forelse ($popularPosts as $post)
                    @include('partials.post-card', ['post' => $post])
                @empty
                    <p class="empty-state">Belum ada berita.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ===== Klasemen liga (data ESPN) ===== --}}
    <section class="sports-widget" aria-label="Klasemen liga sepak bola" data-league-switcher>
        <div class="sports-widget__bg" style="background-image: url('{{ asset('images/sport/stadium.png') }}')"></div>
        <div class="sports-widget__blur"></div>

        @php
            // [label, key, titik tengah kolom (px) relatif terhadap baris]
            $columns = [['GP', 'gp', 243], ['W', 'w', 290.5], ['D', 'd', 331.5], ['L', 'l', 369.5],
                        ['F', 'f', 410.5], ['A', 'a', 457.5], ['GD', 'gd', 505], ['Pts', 'pts', 555]];
        @endphp

        @forelse ($leagues as $index => $league)
            <div class="sports-widget__inner {{ $index === 0 ? 'is-active' : '' }}" data-league>
                <div class="club-table" role="table" aria-label="Klasemen {{ implode(' ', $league['title']) }}">
                    <div class="club-table__row club-table__row--head" role="row">
                        <span class="club-table__club" role="columnheader">Club</span>
                        @foreach ($columns as [$label, $key, $x])
                            <span class="club-table__stat" style="--x: {{ $x }}px" role="columnheader">{{ $label }}</span>
                        @endforeach
                    </div>
                    @foreach ($league['teams'] as $team)
                        <div class="club-table__row" role="row">
                            <span class="club-table__rank" role="cell">{{ $team['rank'] }}</span>
                            @if ($team['logo'])
                                <img src="{{ $team['logo'] }}" alt="" class="club-table__logo" width="24" height="24" loading="lazy">
                            @endif
                            <span class="club-table__name" role="cell">{{ $team['club'] }}</span>
                            @foreach ($columns as [$label, $key, $x])
                                <span class="club-table__stat" style="--x: {{ $x }}px" role="cell">{{ $team[$key] }}</span>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="league-title">
                    <img src="{{ $league['logo'] }}" alt="" width="139" height="139">
                    <h2>@foreach ($league['title'] as $line){{ $line }}@if (! $loop->last)<br>@endif @endforeach</h2>
                    <p class="league-title__season">{{ $league['season'] }}</p>
                </div>
            </div>
        @empty
            <div class="sports-widget__inner is-active">
                <p class="sports-widget__empty">Data klasemen sedang tidak dapat dimuat. Silakan coba lagi nanti.</p>
            </div>
        @endforelse

        @if (count($leagues) > 1)
            <button type="button" class="arrow-btn sports-widget__prev" aria-label="Liga sebelumnya" data-league-prev><i class="fa-solid fa-angle-left"></i></button>
            <button type="button" class="arrow-btn sports-widget__next" aria-label="Liga berikutnya" data-league-next><i class="fa-solid fa-angle-right"></i></button>
        @endif
    </section>

    {{-- ===== New Posts ===== --}}
    <div class="new-posts-bg">
        <section class="container section new-posts">
            <div class="section-header">
                @include('partials.section-title', ['title' => 'New Posts'])
                <a href="{{ route('news.index') }}" class="btn-show-all">Show All <i class="fa-solid fa-angle-right"></i></a>
            </div>
            <div class="grid-2">
                @forelse ($newPosts as $post)
                    @include('partials.post-card-horizontal', ['post' => $post])
                @empty
                    <p class="empty-state">Belum ada berita.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- ===== Cuaca (data Open-Meteo) ===== --}}
    <section class="weather-section" aria-label="Prakiraan cuaca">
        <div class="container weather-grid">
            @if ($weather)
                <div class="weather-widget">
                    <div class="weather-widget__header">
                        <img src="{{ asset($weather['icon']) }}" alt="" class="weather-widget__icon" width="57" height="57">
                        <span class="weather-widget__temp">{{ $weather['temp'] }}<sup>°C</sup></span>
                        <ul class="weather-info">
                            <li>Precipitation: {{ $weather['precipitation'] }}</li>
                            <li>Humidity: {{ $weather['humidity'] }}</li>
                            <li>Wind: {{ $weather['wind'] }}</li>
                        </ul>
                        <div class="weather-widget__city">
                            <h3>{{ $weather['city'] }}</h3>
                            <p>{{ $weather['time'] }}</p>
                        </div>
                    </div>

                    <div class="weather-chart">
                        <svg class="weather-chart__line" viewBox="0 0 744 514" aria-hidden="true">
                            <path d="{{ $weather['chart']['path'] }}" />
                        </svg>
                        @foreach ($weather['chart']['points'] as $point)
                            <span class="weather-chart__value" style="left: {{ $point['x'] }}px; top: {{ $point['y'] }}px">{{ $point['temp'] }}</span>
                        @endforeach
                        <div class="weather-chart__hours">
                            @foreach ($weather['chart']['points'] as $point)
                                <span>{{ $point['hour'] }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="weather-days">
                        @foreach ($weather['days'] as $day)
                            <div class="weather-day {{ $day['active'] ? 'is-active' : '' }}">
                                <span class="weather-day__name">{{ $day['day'] }}</span>
                                <img src="{{ asset($day['icon']) }}" alt="" class="weather-day__icon" height="45">
                                <span class="weather-day__high">{{ $day['high'] }}°</span>
                                <span class="weather-day__low">{{ $day['low'] }}°</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="city-grid">
                    @foreach ($cities as $city)
                        <div class="city-widget city-widget--{{ $city['theme'] }}">
                            <img src="{{ asset($city['image']) }}" alt="" class="city-widget__bg" loading="lazy">
                            <div class="city-widget__overlay"></div>
                            <ul class="weather-info city-widget__info">
                                <li>Precipitation: {{ $city['precipitation'] }}</li>
                                <li>Humidity: {{ $city['humidity'] }}</li>
                                <li>Wind: {{ $city['wind'] }}</li>
                            </ul>
                            <div class="city-widget__city">
                                <h3>{{ $city['city'] }}</h3>
                                <p>{{ $city['time'] }}</p>
                            </div>
                            <div class="city-widget__grade">
                                <img src="{{ asset($city['icon']) }}" alt="" height="57">
                                <span>{{ $city['temp'] }}<sup>°C</sup></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="empty-state">Data cuaca sedang tidak dapat dimuat. Silakan coba lagi nanti.</p>
            @endif
        </div>
    </section>

    {{-- ===== Other News (RSS ANTARA) ===== --}}
    <div class="other-news-bg">
        <section class="container section other-news">
            <div class="section-header">
                @include('partials.section-title', ['title' => 'Other News'])
                <a href="{{ $otherNewsUrl }}" target="_blank" rel="noopener" class="btn-show-all">Show All <i class="fa-solid fa-angle-right"></i></a>
            </div>
            <div class="grid-4">
                @forelse ($otherNews as $item)
                    @include('partials.external-news-card', ['item' => $item])
                @empty
                    <p class="empty-state">Berita luar sedang tidak dapat dimuat.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
