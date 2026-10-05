<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F3F4F6]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIAKAD') — Ilyas Institute</title>

    <script src="https://cdn.tailwindcss.com"></script>
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

        <!-- Memanggil komponen Sidebar -->
        @include('partials.sidebar-dosen')

        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Memanggil komponen Header/Navbar Atas -->
            @include('partials.header-admin')

            <!-- Notifikasi Flash Message -->
            @if (session('success'))
                <div class="mx-6 mt-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Area Konten Utama -->
            <main class="relative flex-1 overflow-y-auto bg-siakad-bg p-4 sm:p-6">
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
</body>

</html>
