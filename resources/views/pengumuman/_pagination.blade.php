@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 text-sm"
        aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-slate-200 px-4 py-2 font-medium text-slate-400">&larr;
                Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">&larr;
                Sebelumnya</a>
        @endif

        <span class="text-slate-500">Halaman <strong class="text-slate-800">{{ $paginator->currentPage() }}</strong>
            dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Berikutnya
                &rarr;</a>
        @else
            <span class="rounded-lg border border-slate-200 px-4 py-2 font-medium text-slate-400">Berikutnya
                &rarr;</span>
        @endif
    </nav>
@endif
