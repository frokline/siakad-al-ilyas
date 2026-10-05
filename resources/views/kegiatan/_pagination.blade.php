@if ($paginator->hasPages())
    <nav class="mt-6 flex items-center justify-between gap-3 text-sm" aria-label="Halaman data">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-400">&larr;
                Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 shadow-sm hover:bg-slate-50">&larr;
                Sebelumnya</a>
        @endif

        <span class="text-slate-500">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Berikutnya
                &rarr;</a>
        @else
            <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-400">Berikutnya
                &rarr;</span>
        @endif
    </nav>
@endif
