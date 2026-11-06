@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb')<li class="breadcrumb-item active">Dashboard</li>@endsection

@section('content')
    <div class="row">
        @foreach ([
            ['bg-info', 'fa-newspaper', $stats['posts'], 'Total Berita', $stats['published'] . ' terbit', route('admin.posts.index')],
            ['bg-success', 'fa-eye', short_number($stats['views']), 'Total Views', short_number($stats['views30']) . ' dalam 30 hari', route('admin.posts.index', ['sort' => 'views'])],
            ['bg-warning', 'fa-users', $stats['users'], 'User Terdaftar', $stats['newUsers'] . ' baru dalam 30 hari', route('admin.users.index')],
            ['bg-danger', 'fa-flag', $stats['openReports'], 'Laporan Terbuka', $stats['comments'] . ' komentar total', route('admin.reports.index')],
        ] as [$bg, $icon, $value, $label, $sub, $url])
            <div class="col-lg-3 col-6">
                <div class="small-box {{ $bg }}">
                    <div class="inner">
                        <h3>{{ $value }}</h3>
                        <p class="mb-0">{{ $label }}</p>
                        <small>{{ $sub }}</small>
                    </div>
                    <div class="icon"><i class="fas {{ $icon }}"></i></div>
                    <a href="{{ $url }}" class="small-box-footer">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Pembaca per hari (30 hari terakhir)</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="viewsChart" aria-label="Grafik jumlah pembaca per hari" role="img"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-layer-group mr-1"></i> Berita per kategori</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="categoryChart" aria-label="Grafik jumlah berita per kategori" role="img"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Berita terbaru</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-sm btn-danger"><i class="fas fa-plus"></i> Tulis Berita</a>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-valign-middle mb-0">
                        <thead>
                            <tr><th>Judul</th><th>Kategori</th><th>Penulis</th><th>Status</th><th class="text-right">Views</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($latestPosts as $post)
                                <tr>
                                    <td class="text-truncate" style="max-width: 280px"><a href="{{ route('admin.posts.edit', $post) }}" title="{{ $post->title }}">{{ $post->title }}</a></td>
                                    <td>{{ $post->category?->name }}</td>
                                    <td>{{ $post->user?->name }}</td>
                                    <td>@include('admin.posts.status', ['post' => $post])</td>
                                    <td class="text-right">{{ number_format($post->views) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada berita.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center"><a href="{{ route('admin.posts.index') }}">Lihat semua berita</a></div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Komentar terbaru</h3></div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @forelse ($latestComments as $comment)
                            <li class="item">
                                <div class="product-img"><img src="{{ $comment->user->avatar_url }}" alt="" class="img-size-50 img-circle"></div>
                                <div class="product-info">
                                    <span class="product-title">{{ $comment->user->name }}
                                        <span class="float-right text-muted text-sm font-weight-normal">{{ $comment->created_at->diffForHumans() }}</span>
                                    </span>
                                    <span class="product-description">{{ $comment->body }}</span>
                                    <small>di <a href="{{ route('news.show', $comment->post) }}#comment-{{ $comment->id }}" target="_blank">{{ \Illuminate\Support\Str::limit($comment->post->title, 60) }}</a></small>
                                </div>
                            </li>
                        @empty
                            <li class="item text-center text-muted py-4">Belum ada komentar.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center"><a href="{{ route('admin.comments.index') }}">Kelola komentar</a></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-fire text-danger mr-1"></i> Berita terpopuler</h3></div>
                <div class="card-body p-0">
                    <ul class="nav flex-column">
                        @foreach ($topPosts as $i => $post)
                            <li class="nav-item">
                                <a href="{{ route('news.show', $post) }}" class="nav-link d-flex align-items-center" target="_blank">
                                    <span class="rank-number">{{ $i + 1 }}</span>
                                    <span class="text-truncate flex-fill mr-2 min-w-0" title="{{ $post->title }}">{{ $post->title }}</span>
                                    <span class="badge badge-light">{{ short_number($post->views) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User terbaru</h3>
                    <div class="card-tools"><span class="badge badge-danger">{{ $stats['newUsers'] }} baru</span></div>
                </div>
                <div class="card-body p-0">
                    <ul class="users-list clearfix">
                        @foreach ($latestUsers as $user)
                            <li>
                                <img src="{{ $user->avatar_url }}" alt="" class="users-list-avatar">
                                <a class="users-list-name" href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a>
                                <span class="users-list-date">{{ $user->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer text-center"><a href="{{ route('admin.users.index') }}">Lihat semua user</a></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const brand = '#f81539';
            const ink = '#6c757d';
            const grid = 'rgba(0,0,0,.06)';
            Chart.defaults.font.family = "'Source Sans Pro', sans-serif";
            Chart.defaults.color = ink;

            new Chart(document.getElementById('viewsChart'), {
                type: 'line',
                data: {
                    labels: @json($viewsChart['labels']),
                    datasets: [{
                        label: 'Views',
                        data: @json($viewsChart['values']),
                        borderColor: brand,
                        backgroundColor: 'rgba(248,21,57,.08)',
                        borderWidth: 2,
                        fill: true,
                        cubicInterpolationMode: 'monotone',
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHitRadius: 12,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                        y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } },
                    },
                },
            });

            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: @json($categoryChart['labels']),
                    datasets: [{
                        label: 'Berita',
                        data: @json($categoryChart['values']),
                        backgroundColor: brand,
                        borderRadius: 4,
                        borderSkipped: 'start',
                        barThickness: 10,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } },
                        y: { grid: { display: false } },
                    },
                },
            });
        })();
    </script>
@endpush
