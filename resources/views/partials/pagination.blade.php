{{-- Tampilan pagination Laravel sesuai desain: 1 2 3 ... 9  Next > --}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, 2, 3, $current - 1, $current, $current + 1, $last])
            ->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
    @endphp
    <nav class="pagination" aria-label="Halaman">
        @if (! $paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination__next" rel="prev"><i class="fa-solid fa-angle-left"></i> Prev</a>
        @endif
        @foreach ($pages as $i => $p)
            @if ($i > 0 && $p - $pages[$i - 1] > 1)
                <span class="pagination__gap">...</span>
            @endif
            <a href="{{ $paginator->url($p) }}" class="pagination__item {{ $p === $current ? 'is-active' : '' }}"
               @if ($p === $current) aria-current="page" @endif>{{ $p }}</a>
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination__next" rel="next">Next <i class="fa-solid fa-angle-right"></i></a>
        @endif
    </nav>
@endif
