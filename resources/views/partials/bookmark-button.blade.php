{{-- Tombol simpan berita (toggle lewat AJAX). Parameter: $post, $class, $label (bool) --}}
@php($bookmarked = $post->isBookmarkedBy(auth()->user()))
<button type="button"
        class="{{ $class }} {{ $bookmarked ? 'is-active' : '' }}"
        data-bookmark="{{ route('news.bookmark', $post) }}"
        aria-pressed="{{ $bookmarked ? 'true' : 'false' }}"
        aria-label="Simpan berita">
    <i class="{{ $bookmarked ? 'fa-solid' : 'fa-regular' }} fa-bookmark"></i>
    @if ($label) <span>Save</span> @endif
</button>
