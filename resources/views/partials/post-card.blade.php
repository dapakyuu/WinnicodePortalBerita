{{-- Kartu berita vertikal. Parameter: $post (App\Models\Post) --}}
<article class="post-card">
    <a href="{{ route('news.show', $post) }}" class="post-card__image">
        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy">
        @if ($post->video_url)
            <span class="post-card__play"><i class="fa-solid fa-play"></i></span>
        @endif
    </a>
    <h3 class="post-card__title"><a href="{{ route('news.show', $post) }}" title="{{ $post->title }}">{{ $post->title }}</a></h3>
    <p class="post-card__excerpt">{{ $post->excerpt }}</p>
    @include('partials.post-author', ['post' => $post])
</article>
