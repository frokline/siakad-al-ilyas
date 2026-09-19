@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman">
        @if ($paginator->previousPageUrl())
            <a href="{{ $paginator->previousPageUrl() }}" class="button button-secondary" rel="prev">
                Sebelumnya
            </a>
        @endif

        <span>
            Halaman {{ $paginator->currentPage() }}
            dari {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->nextPageUrl())
            <a href="{{ $paginator->nextPageUrl() }}" class="button button-secondary" rel="next">
                Berikutnya
            </a>
        @endif
    </nav>
@endif
