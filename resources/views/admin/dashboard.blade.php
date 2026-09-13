@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Ringkasan Latihan')

@section('content')
<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <x-stat label="Paket soal" :value="$packageCount . '/' . \App\Models\Tryout::MAX_PACKAGES" icon="clipboard-list"
        hint="Batas maksimal 2 paket" />
    <x-stat label="Total soal" :value="$totalQuestions" icon="help-circle" hint="Seluruh butir di 2 paket" />
    <x-stat label="Sudah dikuasai" :value="$totalMastered" icon="check-double"
        :hint="$masteryPercentage . '% dari seluruh bank soal'" />
    <x-stat label="Sesi hari ini" :value="$sessionsToday . '/' . $packageCount" icon="calendar"
        :hint="'Total ' . $totalSessions . ' sesi selesai'" />
</div>

<div class="grid gap-5 xl:grid-cols-3 xl:gap-6">
    {{-- Mastery per package --}}
    <section class="card overflow-hidden xl:col-span-2">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-3.5 sm:px-5">
            <h2 class="text-sm font-semibold text-slate-900">Progres penguasaan</h2>
            <a href="{{ route('admin.tryouts.index') }}" class="link text-sm">Kelola paket</a>
        </header>

        @if($packages->isEmpty())
            <x-empty-state
                icon="clipboard-list"
                title="Belum ada paket soal"
                description="Buat paket latihan lalu isi bank soalnya.">
                <x-slot:action>
                    <a href="{{ route('admin.tryouts.create') }}" class="btn btn-primary">
                        <x-icon name="plus" class="size-4" />
                        Buat paket
                    </a>
                </x-slot:action>
            </x-empty-state>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($packages as $package)
                    @php
                        $tryout = $package['tryout'];
                        $mastery = $package['mastery'];
                    @endphp

                    <li class="p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium leading-snug text-slate-900">{{ $tryout->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $tryout->subject }} &middot;
                                    <span class="tabular">{{ $tryout->questions_count }}</span> soal &middot;
                                    <span class="tabular">{{ $tryout->questions_per_session }}</span> soal/hari
                                </p>
                            </div>

                            <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary btn-sm shrink-0">
                                Bank soal
                            </a>
                        </div>

                        <div class="mt-3.5">
                            <div class="flex items-end justify-between gap-3">
                                <span class="text-xs text-slate-500">Dikuasai</span>
                                <span class="text-sm font-medium text-slate-900 tabular">
                                    {{ $mastery['mastered'] }}<span class="text-slate-400">/{{ $mastery['total'] }}</span>
                                    <span class="ml-1 text-xs font-normal text-slate-400">({{ $mastery['percentage'] }}%)</span>
                                </span>
                            </div>

                            <div class="mt-1.5 flex h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full bg-emerald-500" style="width: {{ $mastery['percentage'] }}%"></div>
                                <div class="h-full bg-amber-400"
                                    style="width: {{ $mastery['total'] > 0 ? round(($mastery['learning'] / $mastery['total']) * 100, 1) : 0 }}%"></div>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span class="flex items-center gap-1.5">
                                    <span class="size-2.5 rounded-full bg-emerald-500"></span>
                                    Tuntas <span class="font-medium text-slate-700 tabular">{{ $mastery['mastered'] }}</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="size-2.5 rounded-full bg-amber-400"></span>
                                    Masih dilatih <span class="font-medium text-slate-700 tabular">{{ $mastery['learning'] }}</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="size-2.5 rounded-full bg-slate-200"></span>
                                    Belum dibuka <span class="font-medium text-slate-700 tabular">{{ $mastery['untouched'] }}</span>
                                </span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Side column --}}
    <div class="space-y-5 xl:space-y-6">
        <section class="card card-pad">
            <h2 class="mb-3 text-sm font-semibold text-slate-900">Aksi cepat</h2>

            <div class="space-y-2">
                @if($packageCount < \App\Models\Tryout::MAX_PACKAGES)
                    <a href="{{ route('admin.tryouts.create') }}" class="btn btn-primary w-full justify-start">
                        <x-icon name="plus" class="size-4" />
                        Buat paket soal
                    </a>
                @endif

                <a href="{{ route('admin.tryouts.index') }}" class="btn btn-secondary w-full justify-start">
                    <x-icon name="clipboard-list" class="size-4" />
                    Kelola bank soal
                </a>
                <a href="{{ route('admin.results.index') }}" class="btn btn-secondary w-full justify-start">
                    <x-icon name="chart" class="size-4" />
                    Riwayat sesi
                </a>
            </div>
        </section>

        <section class="card overflow-hidden">
            <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-3.5 sm:px-5">
                <h2 class="text-sm font-semibold text-slate-900">Sesi terakhir</h2>
                <a href="{{ route('admin.results.index') }}" class="link text-sm">Semua</a>
            </header>

            @if($recentSessions->isEmpty())
                <x-empty-state icon="file-text" title="Belum ada sesi selesai" />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($recentSessions as $session)
                        <li>
                            <a href="{{ route('admin.results.show', $session) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-slate-50 sm:px-5">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm text-slate-700">{{ $session->tryout->title }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $session->session_date?->translatedFormat('d M Y') ?? $session->completed_at?->translatedFormat('d M Y') }}
                                    </p>
                                </div>

                                <span class="shrink-0 text-sm font-medium text-slate-900 tabular">{{ round($session->percentage) }}%</span>
                                <x-icon name="chevron-right" class="size-4 shrink-0 text-slate-300" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
@endsection
