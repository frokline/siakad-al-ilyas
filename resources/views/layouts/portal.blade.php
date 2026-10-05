<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F3F4F6]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal SIAKAD') — Ilyas Institute</title>

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

        <!-- Memanggil komponen Sidebar Khusus Portal (Dosen/Mahasiswa) -->
        @include('partials.sidebar-dosen')

        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Memanggil komponen Header/Navbar Atas Khusus Portal -->
            @include('partials.header-portal')

            <!-- Notifikasi Flash Message -->
            @if (session('success'))
                <div class="mx-6 mt-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mx-6 mt-6 p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Area Konten Utama -->
            <main class="flex-1 overflow-y-auto bg-siakad-bg p-4 sm:p-6">
                @yield('content')
            </main>

        </div>
    </div>
</body>

</html>
