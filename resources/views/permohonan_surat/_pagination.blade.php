@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-6 py-4"
        aria-label="Navigasi halaman">
        <p class="hidden text-sm text-slate-600 sm:block">
            Halaman <span class="font-bold text-slate-800">{{ $paginator->currentPage() }}</span>
            dari <span class="font-bold text-slate-800">{{ $paginator->lastPage() }}</span>
        </p>
        <div class="flex flex-1 justify-between gap-3 sm:justify-end">
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex cursor-not-allowed items-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Sebelumnya</a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Berikutnya</a>
            @else
                <span
                    class="inline-flex cursor-not-allowed items-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
