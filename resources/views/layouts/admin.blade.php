<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F3F4F6]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIAKAD') — Ilyas Institute</title>

    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Plugin collapse harus dimuat sebelum Alpine inti, agar x-collapse pada sidebar berfungsi. --}}
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
                            bg: '#F3F4F6'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- Latar belakang gelap saat sidebar di HP terbuka -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

        @if (auth()->user()?->hasRole(\App\Models\Role::ADMIN_AKADEMIK))
            @include('partials.sidebar-admin')
        @elseif (auth()->user()?->hasRole(\App\Models\Role::DOSEN))
            @include('partials.sidebar-dosen')
        @else
            @include('partials.sidebar-mahasiswa')
        @endif

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            @include('partials.header-admin')

            <!-- Area Konten Utama: notifikasi ikut bergulir bersama konten -->
            <main class="relative flex-1 overflow-y-auto bg-siakad-bg p-4 sm:p-6">
                @foreach (['success' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'warning' => 'bg-amber-50 border-amber-200 text-amber-800', 'error' => 'bg-rose-50 border-rose-200 text-rose-700'] as $kunci => $warna)
                    @if (is_string(session($kunci)) && session($kunci) !== '')
                        <div class="mb-6 rounded-lg border p-4 text-sm {{ $warna }}"
                            role="{{ $kunci === 'error' ? 'alert' : 'status' }}">
                            {{ session($kunci) }}
                        </div>
                    @endif
                @endforeach

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700"
                        role="alert">
                        <strong class="font-bold">Periksa kembali data.</strong>
                        <ul class="list-disc list-inside mt-2">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>
    @stack('scripts')
</body>

</html>
