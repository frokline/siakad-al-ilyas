<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F3F4F6]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Otentikasi') — Ilyas Institute</title>

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
</head>

<body class="h-full font-sans text-slate-800 antialiased flex flex-col items-center justify-center bg-siakad-bg">

    <div class="w-full max-w-md absolute top-0">
        @include('partials.alert')
    </div>

    <main class="w-full max-w-md p-6">
        @yield('content')
    </main>

</body>

</html>
