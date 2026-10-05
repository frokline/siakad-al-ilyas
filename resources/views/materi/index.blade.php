<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Dosen') — SIAKAD Ilyas</title>
    <!-- Tailwind CSS (Gunakan file build yang sudah ada di project Anda) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'siakad-dark': '#0f4c3a', // Warna hijau tua ILYAS
                        'siakad-light': '#1b634e',
                        'siakad-gold': '#d4af37', // Garis bawah emas
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .bg-siakad-dark {
            background-color: #0f4c3a;
        }

        .text-siakad-dark {
            color: #0f4c3a;
        }

        .border-siakad-dark {
            border-color: #0f4c3a;
        }

        .focus\:ring-siakad-dark:focus {
            --tw-ring-color: #0f4c3a;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- SKIP LINK -->
    <a href="#konten"
        class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-4 focus:bg-white focus:text-siakad-dark font-bold">
        Langsung ke isi
    </a>

    <!-- NAVBAR PORTAL DOSEN -->
    <header class="bg-siakad-dark text-white border-b-4 border-siakad-gold shadow-md relative z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    <a href="{{ route('kegiatan.kelas') ?? '#' }}" class="group flex flex-col">
                        <span
                            class="text-lg font-black tracking-widest text-white group-hover:text-slate-200 transition-colors">ILYAS
                            INSTITUTE</span>
                        <span
                            class="text-[10px] text-emerald-100/70 uppercase tracking-widest font-medium group-hover:text-emerald-100 transition-colors -mt-1">Portal
                            Kegiatan Kuliah</span>
                    </a>
                </div>

                <!-- Navigasi Desktop -->
                <nav class="hidden md:flex items-center space-x-6">
                    <!--
                        Memanggil Navbar Partial
                        Jika Anda ingin redesain partialnya juga, silakan kirimkan isi partials.navbar-portal
                    -->
                    @include('partials.navbar-portal')
                </nav>

                <!-- Tombol Menu Mobile (Opsional/Jika Anda ingin menambahkannya) -->
                <div class="md:hidden flex items-center">
                    <button type="button" class="text-emerald-100 hover:text-white focus:outline-none"
                        aria-label="Buka menu navigasi">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main id="konten" class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">

        <!-- ALERT FLASH MESSAGES -->
        @if (session('info'))
            <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 p-4 shadow-sm flex items-start gap-3"
                role="status">
                <svg class="h-5 w-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium text-emerald-800">{{ session('info') }}</div>
            </div>
        @endif

        <!-- ALERT ERRORS -->
        @if (isset($errors) && $errors->any())
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-5 shadow-sm" role="alert">
                <div class="flex items-start gap-3 mb-2">
                    <svg class="h-5 w-5 text-rose-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <strong class="text-sm font-bold text-rose-800">Terdapat kesalahan pada data yang Anda
                        kirimkan:</strong>
                </div>
                <ul class="list-disc list-inside space-y-1 ml-8 text-xs font-medium text-rose-700">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- INJECTION HALAMAN -->
        @yield('content')

    </main>

    <!-- FOOTER -->
    <footer class="mt-auto bg-white border-t border-slate-200 py-6">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs font-semibold text-slate-500">
                &copy; {{ date('Y') }} SIAKAD Ilyas Institute
            </p>
            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest text-center md:text-right">
                Akses portal kegiatan dibatasi hanya untuk sivitas akademika terdaftar.
            </p>
        </div>
    </footer>

</body>

</html>
