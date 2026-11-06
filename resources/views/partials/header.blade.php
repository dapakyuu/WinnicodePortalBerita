<header class="site-header container">
    <a href="{{ route('home') }}" class="site-header__logo">
        <img src="{{ asset('images/logo/winnicode.png') }}" alt="WinniCode Garuda Teknologi" width="182" height="73">
    </a>

    <nav class="site-nav" aria-label="Menu utama">
        <div class="dropdown">
            <button type="button" class="site-nav__item {{ request()->routeIs('news.category') ? 'is-active' : '' }}" data-dropdown-toggle aria-expanded="false">
                Categories <i class="fa-solid fa-angle-down"></i>
            </button>
            <ul class="dropdown__menu dropdown__menu--grid">
                @foreach ($navCategories as $category)
                    <li><a href="{{ route('news.category', $category) }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('news.index', ['sort' => 'popular']) }}" class="site-nav__item {{ request()->routeIs('news.index') && request('sort') === 'popular' ? 'is-active' : '' }}">Trendings</a>
        <a href="{{ route('about') }}#contact" class="site-nav__item">Contact Us</a>
        <a href="{{ route('about') }}" class="site-nav__item {{ request()->routeIs('about') ? 'is-active' : '' }}">About Us</a>
    </nav>

    <div class="site-header__right">
        <form class="search" action="{{ route('news.index') }}" method="GET" role="search">
            <div class="dropdown">
                <button type="button" class="search__filter" aria-label="Urutkan hasil" data-dropdown-toggle aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="dropdown__menu search__sort">
                    @foreach (['new' => 'Terbaru', 'popular' => 'Terpopuler', 'top' => 'Rating tertinggi'] as $value => $label)
                        <label><input type="radio" name="sort" value="{{ $value }}" @checked(request('sort', 'new') === $value)> {{ $label }}</label>
                    @endforeach
                </div>
            </div>
            <input type="search" name="q" value="{{ request('q') }}" class="search__input" placeholder="Search Anything" aria-label="Cari berita" maxlength="100">
            <button type="submit" class="search__submit" aria-label="Cari">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <div class="user-section">
            <div class="dropdown dropdown--right">
                <button type="button" class="user" data-dropdown-toggle aria-expanded="false">
                    @auth
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="user__avatar" width="48" height="48">
                        <span class="user__name">{{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}</span>
                    @else
                        <span class="user__avatar user__avatar--guest"><i class="fa-solid fa-user-tie"></i></span>
                        <span class="user__name">User</span>
                    @endauth
                    <i class="fa-solid fa-angle-down user__caret"></i>
                </button>
                <ul class="dropdown__menu">
                    @auth
                        @if (auth()->user()->isAdmin())
                            <li><a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge"></i> Dashboard Admin</a></li>
                        @endif
                        <li><a href="{{ route('profile.marked') }}"><i class="fa-regular fa-user"></i> Profile</a></li>
                        <li><a href="{{ route('profile.create') }}"><i class="fa-regular fa-paper-plane"></i> Send Post</a></li>
                        <li><a href="{{ route('profile.posts') }}"><i class="fa-regular fa-newspaper"></i> My Posts</a></li>
                        <li><a href="{{ route('profile.edit') }}"><i class="fa-regular fa-pen-to-square"></i> Edit Profile</a></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                        <li><a href="{{ route('register') }}"><i class="fa-solid fa-user-plus"></i> Register</a></li>
                    @endauth
                </ul>
            </div>
            <a href="{{ route('profile.marked') }}" class="icon-btn" aria-label="Berita tersimpan">
                <i class="fa-regular fa-bookmark"></i>
            </a>
        </div>
    </div>
</header>
