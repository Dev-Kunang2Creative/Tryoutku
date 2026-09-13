<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Portal Latihan') &middot; Tryoutku</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-full flex-col">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between gap-4 px-4 sm:h-16 sm:px-6">
            <a href="{{ route('student.dashboard') }}" class="flex min-w-0 items-center gap-2.5">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white">
                    <x-icon name="graduation-cap" class="size-[1.125rem]" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold leading-tight tracking-tight text-slate-900">Latihan Harian</span>
                    <span class="hidden text-xs leading-tight text-slate-500 sm:block">Latihan IPA & Bahasa Inggris</span>
                </span>
            </a>

            @auth
                <div class="flex items-center gap-1 sm:gap-2">
                    {{-- Desktop navigation --}}
                    <nav class="hidden items-center gap-1 md:flex">
                        <a href="{{ route('student.dashboard') }}"
                            class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('student.dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            Latihan Hari Ini
                        </a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                                <x-icon name="shield-check" class="size-4" />
                                Kelola Soal
                            </a>
                        @endif
                    </nav>

                    <span class="mx-1 hidden h-6 w-px bg-slate-200 md:block"></span>

                    <div class="hidden items-center gap-2.5 md:flex">
                        <span class="flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="max-w-[10rem] truncate text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="hidden md:block">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" title="Keluar dari akun">
                            <x-icon name="log-out" class="size-4" />
                            <span class="sr-only lg:not-sr-only">Keluar</span>
                        </button>
                    </form>

                    {{-- Mobile trigger --}}
                    <button type="button" id="navToggle" aria-expanded="false" aria-controls="mobileNav"
                        class="btn btn-ghost -mr-2 px-2 md:hidden">
                        <x-icon name="menu" id="navToggleOpen" class="size-5" />
                        <x-icon name="x" id="navToggleClose" class="size-5 hidden" />
                        <span class="sr-only">Buka menu navigasi</span>
                    </button>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
            @endauth
        </div>

        @auth
            {{-- Mobile navigation panel --}}
            <nav id="mobileNav" class="hidden border-t border-slate-200 bg-white px-4 py-3 md:hidden">
                <div class="mb-3 flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2.5">
                    <span class="flex size-9 items-center justify-center rounded-full bg-white text-sm font-semibold text-slate-600 ring-1 ring-slate-200">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</span>
                        <span class="block truncate text-xs text-slate-500">Latihan harian</span>
                    </span>
                </div>

                <a href="{{ route('student.dashboard') }}"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('student.dashboard') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <x-icon name="clipboard-list" class="size-[1.125rem]" />
                    Latihan Hari Ini
                </a>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                        <x-icon name="shield-check" class="size-[1.125rem]" />
                        Kelola Soal
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="mt-2 border-t border-slate-100 pt-2">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                        <x-icon name="log-out" class="size-[1.125rem]" />
                        Keluar
                    </button>
                </form>
            </nav>
        @endauth
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 sm:py-8">
        <x-flash-messages />

        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-5 text-center text-xs leading-relaxed text-slate-500 sm:px-6">
            &copy; {{ date('Y') }} Portal Latihan Internal Latihan IPA & Bahasa Inggris &middot; Akses terbatas untuk anggota tim.
        </div>
    </footer>

    <script>
        (() => {
            const toggle = document.getElementById('navToggle');
            const panel = document.getElementById('mobileNav');

            if (!toggle || !panel) {
                return;
            }

            const openIcon = document.getElementById('navToggleOpen');
            const closeIcon = document.getElementById('navToggleClose');

            toggle.addEventListener('click', () => {
                const isOpen = panel.classList.toggle('hidden') === false;

                toggle.setAttribute('aria-expanded', String(isOpen));
                openIcon.classList.toggle('hidden', isOpen);
                closeIcon.classList.toggle('hidden', !isOpen);
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
