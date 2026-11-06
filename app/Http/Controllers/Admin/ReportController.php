<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Laporan berita dari pembaca (tombol "Report" di halaman berita). */
class ReportController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $status = $request->input('status', 'open');

        $reports = Report::with(['user:id,name,username', 'post:id,title,slug,user_id', 'post.user:id,name'])
            ->when($status === 'open', fn ($q) => $q->whereNull('resolved_at'))
            ->when($status === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'status' => $status,
            'counts' => [
                'open' => Report::whereNull('resolved_at')->count(),
                'resolved' => Report::whereNotNull('resolved_at')->count(),
            ],
        ]);
    }

    /** Tandai selesai / buka kembali. */
    public function toggle(Report $report): RedirectResponse
    {
        $report->update(['resolved_at' => $report->resolved_at ? null : now()]);

        return back()->with('status', $report->resolved_at ? 'Laporan ditandai selesai.' : 'Laporan dibuka kembali.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        $report->delete();

        return back()->with('status', 'Laporan berhasil dihapus.');
    }

    /** Hapus berita yang dilaporkan (semua laporannya ikut terhapus). */
    public function destroyPost(Report $report): RedirectResponse
    {
        $post = $report->post;
        $this->deleteUpload($post->image);
        $post->delete();

        return back()->with('status', "Berita \"{$post->title}\" yang dilaporkan berhasil dihapus.");
    }
}
