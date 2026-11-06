@extends('admin.layouts.app')

@section('title', 'Laporan Berita')
@section('breadcrumb')<li class="breadcrumb-item active">Laporan</li>@endsection

@section('content')
    <div class="card card-danger card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs">
                @foreach (['open' => ['Belum ditangani', $counts['open']], 'resolved' => ['Selesai', $counts['resolved']], 'all' => ['Semua', $counts['open'] + $counts['resolved']]] as $key => [$label, $count])
                    <li class="nav-item">
                        <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.reports.index', ['status' => $key]) }}">
                            {{ $label }} <span class="badge {{ $key === 'open' && $count ? 'badge-warning' : 'badge-light' }}">{{ $count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-valign-middle mb-0">
                <thead>
                    <tr><th>Berita</th><th>Alasan</th><th>Pelapor</th><th>Waktu</th><th>Status</th><th class="text-right" style="width: 170px">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td class="text-truncate" style="max-width: 280px">
                                <a href="{{ route('news.show', $report->post) }}" target="_blank" class="font-weight-bold" title="{{ $report->post->title }}">{{ $report->post->title }}</a>
                                <small class="d-block text-muted">oleh {{ $report->post->user?->name }}</small>
                            </td>
                            <td>{{ $report->reason }}</td>
                            <td>{{ $report->user->name }} <small class="text-muted d-block">{{ '@' . $report->user->username }}</small></td>
                            <td><small title="{{ $report->created_at->format('d M Y H:i') }}">{{ $report->created_at->diffForHumans() }}</small></td>
                            <td>
                                @if ($report->resolved_at)
                                    <span class="badge badge-success" title="{{ $report->resolved_at->format('d M Y H:i') }}"><i class="fas fa-check"></i> Selesai</span>
                                @else
                                    <span class="badge badge-warning"><i class="far fa-clock"></i> Terbuka</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                <form action="{{ route('admin.reports.toggle', $report) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $report->resolved_at ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $report->resolved_at ? 'Buka kembali' : 'Tandai selesai' }}">
                                        <i class="fas {{ $report->resolved_at ? 'fa-rotate-left' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.posts.edit', $report->post) }}" class="btn btn-sm btn-outline-primary" title="Edit berita"><i class="fas fa-pen"></i></a>
                                @include('admin.partials.delete-button', ['action' => route('admin.reports.destroy-post', $report), 'message' => "Hapus berita \"{$report->post->title}\" yang dilaporkan? Semua laporan untuk berita ini ikut terhapus.", 'class' => 'btn-sm btn-danger'])
                                @include('admin.partials.delete-button', ['action' => route('admin.reports.destroy', $report), 'message' => 'Hapus laporan ini saja (berita tetap ada)?', 'class' => 'btn-sm btn-outline-danger'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5"><i class="far fa-face-smile fa-2x d-block mb-2"></i>Tidak ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reports->hasPages())
            <div class="card-footer clearfix">{{ $reports->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
@endsection
