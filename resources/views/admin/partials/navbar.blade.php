<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Buka/tutup menu"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('home') }}" class="nav-link" target="_blank"><i class="fas fa-arrow-up-right-from-square mr-1"></i> Lihat Website</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.posts.create') }}" class="nav-link"><i class="fas fa-plus mr-1"></i> Tulis Berita</a>
        </li>
    </ul>

    <form class="form-inline ml-3 d-none d-md-flex" action="{{ route('admin.posts.index') }}" method="GET">
        <div class="input-group input-group-sm">
            <input class="form-control form-control-navbar" type="search" name="q" placeholder="Cari judul berita..." aria-label="Cari berita" value="{{ request()->routeIs('admin.posts.index') ? request('q') : '' }}">
            <div class="input-group-append">
                <button class="btn btn-navbar" type="submit" aria-label="Cari"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </form>

    <ul class="navbar-nav ml-auto">
        @if ($openReports = \App\Models\Report::whereNull('resolved_at')->count())
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.reports.index') }}" title="{{ $openReports }} laporan belum ditangani">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-danger navbar-badge">{{ $openReports }}</span>
                </a>
            </li>
        @endif
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="{{ auth()->user()->avatar_url }}" class="user-image img-circle elevation-1" alt="">
                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <li class="user-header bg-winni">
                    <img src="{{ auth()->user()->avatar_url }}" class="img-circle elevation-2" alt="">
                    <p>{{ auth()->user()->name }} <small>@ {{ auth()->user()->username }} · Administrator</small></p>
                </li>
                <li class="user-footer">
                    <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">Profil</a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline float-right">
                        @csrf
                        <button type="submit" class="btn btn-default btn-flat">Logout</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
