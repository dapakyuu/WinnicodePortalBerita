@extends('layouts.app')

@section('title', 'Profile - Marked')
@section('body_class', 'page-gradient')

@section('content')
    @include('partials.profile-top', ['user' => $user, 'active' => 'marked'])

    <section class="container profile-content">
        @if ($posts->isEmpty())
            <div class="empty-box">
                <i class="fa-regular fa-bookmark"></i>
                <p>Belum ada berita yang disimpan. Tekan ikon <i class="fa-regular fa-bookmark"></i> pada berita untuk menyimpannya.</p>
                <a href="{{ route('news.index') }}" class="btn-solid">Jelajahi berita</a>
            </div>
        @else
            <div class="grid-4 news-list__grid">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
            {{ $posts->links() }}
        @endif
    </section>
@endsection
