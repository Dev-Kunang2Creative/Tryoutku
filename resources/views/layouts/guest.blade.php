<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Masuk') &middot; Tryoutku</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50">
    <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6 sm:py-12">
        <div class="w-full max-w-md">
            <div class="mb-7 flex flex-col items-center text-center">
                <span class="mb-4 flex size-11 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <x-icon name="graduation-cap" class="size-6" />
                </span>

                <h1 class="text-xl font-semibold tracking-tight text-slate-900">@yield('heading', 'Portal Latihan Harian')</h1>
                <p class="mt-1.5 max-w-xs text-sm leading-relaxed text-slate-500">@yield('subheading')</p>
            </div>

            <x-flash-messages />

            <div class="card card-pad">
                @yield('content')
            </div>

            <p class="mt-6 flex items-center justify-center gap-1.5 text-center text-xs text-slate-400">
                <x-icon name="lock" class="size-3.5" />
                Portal latihan pribadi
            </p>
        </div>
    </main>
</body>
</html>
