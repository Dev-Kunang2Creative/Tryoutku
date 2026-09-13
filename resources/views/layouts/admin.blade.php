<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Kelola Soal') &middot; Tryoutku</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full">
    {{-- Backdrop, mobile only --}}
    <div id="sidebarBackdrop" class="fixed inset-0 z-40 hidden bg-slate-900/40 lg:hidden" aria-hidden="true"></div>

    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-[17rem] -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-out lg:translate-x-0">

        <div class="flex h-14 shrink-0 items-center justify-between gap-2 border-b border-slate-200 px-4 sm:h-16">
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white">
                    <x-icon name="shield-check" class="size-[1.125rem]" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold leading-tight tracking-tight text-slate-900">Kelola Soal</span>
                    <span class="block truncate text-xs leading-tight text-slate-500">Panel pengelolaan</span>
                </span>
            </a>

            <button type="button" id="sidebarClose" class="btn btn-ghost -mr-2 px-2 lg:hidden">
                <x-icon name="x" class="size-5" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Manajemen</p>

            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
                    ['route' => 'admin.tryouts.index', 'active' => 'admin.tryouts.*', 'icon' => 'clipboard-list', 'label' => 'Paket Soal'],
                    ['route' => 'admin.results.index', 'active' => 'admin.results.*', 'icon' => 'chart', 'label' => 'Riwayat Sesi'],
                ];
            @endphp

            <ul class="space-y-1">
                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['active']); @endphp
                    <li>
                        <a href="{{ route($item['route']) }}" @if($isActive) aria-current="page" @endif
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <x-icon :name="$item['icon']" class="size-[1.125rem] shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="px-3 pb-2 pt-6 text-xs font-semibold uppercase tracking-wide text-slate-400">Tampilan Anak</p>

            <a href="{{ route('student.dashboard') }}" target="_blank" rel="noopener"
                class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                <x-icon name="external-link" class="size-[1.125rem] shrink-0" />
                Buka Halaman Latihan
            </a>
        </nav>

        <div class="shrink-0 border-t border-slate-200 p-3">
            <div class="flex items-center gap-2.5 rounded-lg px-2 py-1.5">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</span>
                    <span class="block truncate text-xs text-slate-500">Pengelola</span>
                </span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm px-2" title="Keluar dari akun">
                        <x-icon name="log-out" class="size-[1.125rem]" />
                        <span class="sr-only">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-h-full flex-col lg:pl-[17rem]">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
            <div class="flex h-14 items-center gap-3 px-4 sm:h-16 sm:px-6">
                <button type="button" id="sidebarOpen" aria-controls="sidebar" aria-expanded="false"
                    class="btn btn-ghost -ml-2 px-2 lg:hidden">
                    <x-icon name="menu" class="size-5" />
                    <span class="sr-only">Buka menu navigasi</span>
                </button>

                <h1 class="min-w-0 flex-1 truncate text-base font-semibold tracking-tight text-slate-900 sm:text-lg">
                    @yield('header_title', 'Dashboard')
                </h1>

                <span class="hidden shrink-0 items-center gap-1.5 text-xs text-slate-500 sm:flex">
                    <x-icon name="calendar" class="size-4" />
                    {{ now()->translatedFormat('d M Y') }}
                </span>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 sm:py-8">
            <x-flash-messages />

            @yield('content')
        </main>
    </div>

    <script>
        (() => {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const openBtn = document.getElementById('sidebarOpen');
            const closeBtn = document.getElementById('sidebarClose');

            const setOpen = (isOpen) => {
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                backdrop.classList.toggle('hidden', !isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
                openBtn.setAttribute('aria-expanded', String(isOpen));
            };

            openBtn.addEventListener('click', () => setOpen(true));
            closeBtn.addEventListener('click', () => setOpen(false));
            backdrop.addEventListener('click', () => setOpen(false));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !backdrop.classList.contains('hidden')) {
                    setOpen(false);
                }
            });

            // Reset state when crossing into the desktop breakpoint.
            window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
                if (event.matches) {
                    setOpen(false);
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
