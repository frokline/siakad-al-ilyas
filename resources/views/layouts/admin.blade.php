<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F5F7FA]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SIAKAD') — ILYAS INSTITUT</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        siakad: {
                            dark: '#065F46',
                            active: '#047857',
                            accent: '#F59E0B',
                            bg: '#F5F7FA',
                            surface: '#FFFFFF',
                            muted: '#64748B'
                        }
                    },
                    boxShadow: {
                        soft: '0 4px 24px rgba(15, 23, 42, 0.04)'
                    }
                }
            }
        };
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        ::selection {
            background: #D1FAE5;
            color: #065F46;
        }

        :focus-visible {
            outline: 3px solid rgba(16, 185, 129, 0.65);
            outline-offset: 3px;
        }

        .siakad-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #CBD5E1 transparent;
        }

        .siakad-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .siakad-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 999px;
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-full bg-siakad-bg font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }"
    @keydown.escape.window="sidebarOpen = false">
    <a href="#konten-utama"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-white focus:px-4 focus:py-3 focus:font-semibold focus:text-emerald-800 focus:shadow-lg">
        Lewati ke konten utama
    </a>

    <div class="flex h-screen min-h-0 overflow-hidden">

        {{-- Overlay sidebar pada perangkat mobile --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-[2px] lg:hidden" aria-hidden="true"></div>

        {{-- Sidebar sesuai peran pengguna --}}
        @if (auth()->user()?->hasRole(\App\Models\Role::ADMIN_AKADEMIK))
            @include('partials.sidebar-admin')
        @elseif (auth()->user()?->hasRole(\App\Models\Role::DOSEN))
            @include('partials.sidebar-dosen')
        @elseif (auth()->user()?->hasRole('admin_keuangan'))
            @include('partials.sidebar-keuangan')
        @else
            @include('partials.sidebar-mahasiswa')
        @endif

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            {{-- Header utama --}}
            @include('partials.header-admin')

            {{-- Area konten --}}
            <main id="konten-utama" tabindex="-1" class="siakad-scrollbar relative min-h-0 flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-[1800px] p-4 sm:p-6 lg:p-8">

                    {{-- Judul halaman opsional --}}
                    @hasSection('page-heading')
                        <div class="mb-6">
                            @yield('page-heading')
                        </div>
                    @endif

                    {{-- Notifikasi dari session --}}
                    @foreach ([
        'success' => [
            'title' => 'Berhasil',
            'class' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'icon' => '✓',
        ],
        'warning' => [
            'title' => 'Perhatian',
            'class' => 'border-amber-200 bg-amber-50 text-amber-900',
            'icon' => '!',
        ],
        'error' => [
            'title' => 'Terjadi kesalahan',
            'class' => 'border-rose-200 bg-rose-50 text-rose-900',
            'icon' => '×',
        ],
    ] as $kunci => $notifikasi)
                        @if (is_string(session($kunci)) && session($kunci) !== '')
                            <div class="mb-5 flex items-start gap-3 rounded-2xl border p-4 shadow-sm {{ $notifikasi['class'] }}"
                                role="{{ $kunci === 'error' ? 'alert' : 'status' }}">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/80 text-lg font-bold"
                                    aria-hidden="true">
                                    {{ $notifikasi['icon'] }}
                                </span>

                                <div class="min-w-0 flex-1 pt-1">
                                    <p class="font-semibold">
                                        {{ $notifikasi['title'] }}
                                    </p>
                                    <p class="mt-1 text-sm leading-6">
                                        {{ session($kunci) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Ringkasan kesalahan validasi --}}
                    @if (isset($errors) && $errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-white p-5 shadow-sm" role="alert"
                            aria-labelledby="validation-error-title">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 8v4m0 4h.01" />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h2 id="validation-error-title" class="font-semibold text-rose-800">
                                        Periksa kembali data Anda
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-600">
                                        Ada {{ $errors->count() }} kesalahan yang perlu diperbaiki.
                                    </p>

                                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-rose-700">
                                        @foreach ($errors->all() as $pesan)
                                            <li>{{ $pesan }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Isi setiap halaman --}}
                    @yield('content')

                </div>
            </main>

        </div>
    </div>

    @stack('scripts')
</body>

</html>
