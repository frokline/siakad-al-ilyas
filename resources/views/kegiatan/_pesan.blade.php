@if (session('info'))
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
        {{ session('info') }}
    </div>
@endif
