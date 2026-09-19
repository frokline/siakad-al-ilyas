@if ($paginator->hasPages())
    <nav class="mhs-pagination" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="help" aria-disabled="true">Sebelumnya</span>
        @else
            <a class="button secondary small" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                Sebelumnya
            </a>
        @endif

        <span class="help">
            Halaman {{ $paginator->currentPage() }}
            dari {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a class="button secondary small" href="{{ $paginator->nextPageUrl() }}" rel="next">
                Berikutnya
            </a>
        @else
            <span class="help" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
