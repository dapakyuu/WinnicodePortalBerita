<div class="post-card__info">
    <img src="{{ $post->user->avatar_url }}" alt="" class="post-card__avatar" width="44" height="44" loading="lazy">
    <div class="post-card__meta">
        <span class="post-card__author">{{ $post->user->name }}</span>
        <span class="post-card__date">{{ $post->date_label }}</span>
    </div>
    @include('partials.bookmark-button', ['post' => $post, 'class' => 'post-card__bookmark', 'label' => false])
</div>
