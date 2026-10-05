@if ($paginator->hasPages())
    <nav class="flex items-center justify-between text-xs text-slate-600" aria-label="Navigasi halaman">
        <div>
            @if ($paginator->onFirstPage())
                <span
                    class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 font-medium text-slate-400 cursor-not-allowed">Sebelumnya</span>
            @else
                <a class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                    href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    Sebelumnya
                </a>
            @endif
        </div>

        <span class="font-medium">
            Halaman <strong class="text-slate-800">{{ $paginator->currentPage() }}</strong> dari <strong
                class="text-slate-800">{{ $paginator->lastPage() }}</strong>
        </span>

        <div>
            @if ($paginator->hasMorePages())
                <a class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                    href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Berikutnya
                </a>
            @else
                <span
                    class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 font-medium text-slate-400 cursor-not-allowed">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
