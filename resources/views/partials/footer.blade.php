<footer class="site-footer">
    <div class="footer-panel">
        <div class="footer-panel__about">
            <img src="{{ asset('images/logo/winnicode.png') }}" alt="WinniCode Garuda Teknologi" class="footer-panel__logo" width="266" height="107">
            @include('partials.section-title', ['title' => config('company.name')])
            <p class="footer-panel__text">{{ config('company.summary') }}</p>
        </div>

        <div class="footer-panel__links">
            @include('partials.section-title', ['title' => 'Categories'])
            <ul class="footer-panel__menu">
                @foreach ($footerCategories as $category)
                    <li><a href="{{ route('news.category', $category) }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>

            @include('partials.section-title', ['title' => 'Social Network'])
            <a href="{{ config('company.instagram') }}" target="_blank" rel="noopener" class="btn-instagram">
                <i class="fa-brands fa-instagram"></i> Instagram
            </a>
        </div>

        <div class="footer-panel__copyright">All Copyright (C) 2025 Reserved</div>
    </div>

    <div class="footer-col">
        @include('partials.section-title', ['title' => 'New Comments'])
        <div class="footer-comments">
            @forelse ($footerComments as $comment)
                <a href="{{ route('news.show', $comment->post) }}#comment-{{ $comment->id }}" class="footer-comment">
                    <p class="footer-comment__name">{{ $comment->user->name }}</p>
                    <p class="footer-comment__text">{{ $comment->body }}</p>
                </a>
            @empty
                <p class="empty-state">Belum ada komentar.</p>
            @endforelse
        </div>
    </div>

    <div class="footer-col">
        @include('partials.section-title', ['title' => 'Follow Us On Instagram'])
        <div class="footer-instagram">
            @foreach ($footerInstagram as $item)
                <a href="{{ config('company.instagram') }}" target="_blank" rel="noopener" class="footer-instagram__item">
                    <img src="{{ asset($item['image']) }}" alt="" loading="lazy"
                         @isset($item['position']) style="object-position: {{ $item['position'] }}" @endisset>
                </a>
            @endforeach
        </div>
    </div>
</footer>
