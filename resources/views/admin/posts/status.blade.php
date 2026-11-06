@if (! $post->published_at)
    <span class="badge badge-secondary"><i class="far fa-file-lines"></i> Draft</span>
@elseif ($post->published_at->isFuture())
    <span class="badge badge-info" title="{{ $post->published_at->format('d M Y H:i') }}"><i class="far fa-clock"></i> Terjadwal</span>
@else
    <span class="badge badge-success"><i class="fas fa-check"></i> Terbit</span>
@endif
@if ($post->is_featured)
    <span class="badge badge-warning" title="Tampil di slider beranda"><i class="fas fa-star"></i></span>
@endif
