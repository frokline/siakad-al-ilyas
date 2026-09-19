@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Halaman daftar">
        @if ($paginator->onFirstPage())
        <span>Sebelumnya</span>@else<a rel="prev" href="{{ $paginator->previousPageUrl() }}">Sebelumnya</a>
        @endif
        <span>Halaman {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }} · {{ $paginator->total() }}
            data</span>
        @if ($paginator->hasMorePages())
        <a rel="next" href="{{ $paginator->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>
        @endif
    </nav>
@endif
