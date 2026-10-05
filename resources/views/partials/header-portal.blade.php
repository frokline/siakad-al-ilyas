<header class="flex h-16 shrink-0 items-center justify-between bg-white px-4 sm:px-6 shadow-sm z-10">
    <div class="flex items-center gap-4 flex-1">
        <!-- Tombol Hamburger untuk HP -->
        <button @click="sidebarOpen = true" class="text-slate-500 hover:text-siakad-dark lg:hidden" aria-label="Buka Menu">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <!-- Kotak Pencarian Global/Portal -->
        <div class="relative hidden w-full max-w-md sm:block">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" placeholder="Pencarian portal..."
                class="w-full rounded-md bg-slate-100 py-2 pl-9 pr-4 text-sm text-slate-700 outline-none transition-all focus:bg-white focus:ring-1 focus:ring-slate-300 border border-transparent focus:border-slate-300 placeholder-slate-400">
        </div>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-slate-600 hidden sm:inline-block">
            {{ auth('web')->user()->nama ?? 'Pengguna' }}
        </span>
    </div>
</header>
