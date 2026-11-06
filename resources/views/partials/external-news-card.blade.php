{{-- Kartu berita dari sumber luar (RSS). Parameter: $item (array) --}}
<article class="post-card">
    <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="post-card__image">
        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
    </a>
    <h3 class="post-card__title"><a href="{{ $item['url'] }}" target="_blank" rel="noopener" title="{{ $item['title'] }}">{{ $item['title'] }}</a></h3>
    <p class="post-card__excerpt">{{ $item['excerpt'] }}</p>
    <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="post-card__info post-card__info--link">
        Visit The Link <i class="fa-solid fa-angle-right"></i>
    </a>
</article>
