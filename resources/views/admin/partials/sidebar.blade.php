@php
    $menu = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'fa-gauge-high', 'label' => 'Dashboard'],
        ['header' => 'KONTEN'],
        ['route' => 'admin.posts.index', 'match' => 'admin.posts.*', 'icon' => 'fa-newspaper', 'label' => 'Berita', 'count' => \App\Models\Post::count()],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'fa-layer-group', 'label' => 'Kategori'],
        ['route' => 'admin.tags.index', 'match' => 'admin.tags.*', 'icon' => 'fa-hashtag', 'label' => 'Tag'],
        ['route' => 'admin.comments.index', 'match' => 'admin.comments.*', 'icon' => 'fa-comments', 'label' => 'Komentar'],
        ['header' => 'PENGGUNA'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'fa-users', 'label' => 'User'],
        ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'icon' => 'fa-flag', 'label' => 'Laporan', 'badge' => \App\Models\Report::whereNull('resolved_at')->count()],
    ];
@endphp
<aside class="main-sidebar sidebar-dark-danger elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <span class="brand-image brand-mark elevation-2">W</span>
        <span class="brand-text">WinniCode <small class="text-muted">Admin</small></span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ auth()->user()->avatar_url }}" class="img-circle elevation-2" alt="">
            </div>
            <div class="info">
                <a href="{{ route('profile.marked') }}" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                @foreach ($menu as $item)
                    @isset($item['header'])
                        <li class="nav-header">{{ $item['header'] }}</li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                                <i class="nav-icon fas {{ $item['icon'] }}"></i>
                                <p>
                                    {{ $item['label'] }}
                                    @if (! empty($item['badge']))
                                        <span class="right badge badge-warning">{{ $item['badge'] }}</span>
                                    @elseif (isset($item['count']))
                                        <span class="right badge badge-secondary">{{ $item['count'] }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                    @endisset
                @endforeach
                <li class="nav-header">LAINNYA</li>
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link" target="_blank">
                        <i class="nav-icon fas fa-globe"></i>
                        <p>Lihat Website</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
