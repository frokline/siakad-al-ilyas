@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-slate-200 bg-slate-50/50 px-6 py-4 mt-4"
        aria-label="Pagination">
        <div class="hidden sm:block">
            <p class="text-sm text-slate-600">
                Halaman <span class="font-bold text-slate-800">{{ $paginator->currentPage() }}</span> dari <span
                    class="font-bold text-slate-800">{{ $paginator->lastPage() }}</span>
                &middot; Total <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> data
            </p>
        </div>
        <div class="flex flex-1 justify-between sm:justify-end gap-3">
            @if ($paginator->onFirstPage())
                <span
                    class="relative inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="relative inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="relative inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                    Berikutnya
                </a>
            @else
                <span
                    class="relative inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                    Berikutnya
                </span>
            @endif
        </div>
    </nav>
@endif
