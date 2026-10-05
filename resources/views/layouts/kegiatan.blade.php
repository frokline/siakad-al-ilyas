<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Dosen') — SIAKAD Ilyas</title>

    <!-- Tailwind CSS & AlpineJS -->
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
                            W
                            accent: '#F59E0B',
                            bg: '#F3F4F6'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="h-full font-sans text-slate-800 antialiased flex flex-col bg-slate-50">
    <!-- HEADER & NAVBAR -->
    <header class="bg-siakad-dark text-white border-b-4 border-siakad-accent shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between py-4 gap-4">
                <!-- Brand -->
                <a href="{{ route('kegiatan.kelas') }}" class="flex flex-col">
                    <span class="text-base font-black tracking-wider text-white">ILYAS INSTITUTE</span>
                    <span class="text-[10px] text-emerald-200 uppercase tracking-widest font-medium">Portal Kegiatan
                        Kuliah</span>
                </a>

                <!-- Navbar Portal -->
                @include('partials.navbar-portal')
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main id="konten" class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @if (session('info'))
            <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm shadow-sm"
                role="status">
                {{ session('info') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm shadow-sm"
                role="alert">
                <strong>Periksa kembali data Anda.</strong>
                <ul class="list-disc list-inside mt-1 text-xs">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="mt-auto bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        SIAKAD Ilyas Institute &middot; Portal Dosen & Kegiatan Kuliah
    </footer>
</body>

</html>
