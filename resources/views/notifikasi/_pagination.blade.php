@if ($paginator->hasPages())
    <nav class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Navigasi halaman">
        <p class="text-xs text-slate-500">
            Menampilkan <span
                class="font-semibold text-slate-700">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span> notifikasi
        </p>
        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-400">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-siakad-dark hover:text-siakad-dark">Sebelumnya</a>
            @endif
            <span class="px-2 text-sm text-slate-600">Halaman {{ $paginator->currentPage() }} /
                {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-siakad-dark hover:text-siakad-dark">Berikutnya</a>
            @else
                <span
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-400">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
