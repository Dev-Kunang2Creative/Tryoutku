@extends('layouts.app')

@section('title', 'Latihan Hari Ini')

@section('content')
{{-- Today's header --}}
<section class="mb-7">
    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
        {{ now()->translatedFormat('l, d F Y') }}
    </p>

    <h1 class="mt-1.5 text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
        Halo, {{ auth()->user()->name }}
    </h1>

    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
        @if($doneToday >= $packageCount && $packageCount > 0)
            Latihan hari ini sudah selesai semua. Sampai besok!
        @else
            Selesaikan {{ $packageCount }} paket latihan hari ini. Soal yang masih salah akan muncul lagi besok.
        @endif
    </p>

    <dl class="mt-5 grid grid-cols-2 gap-3 sm:gap-4">
        <div class="card px-4 py-3.5">
            <dt class="text-xs text-slate-500">Paket selesai hari ini</dt>
            <dd class="mt-1 text-xl font-semibold tracking-tight text-slate-900 tabular">
                {{ $doneToday }}<span class="text-base font-normal text-slate-400">/{{ $packageCount }}</span>
            </dd>
        </div>
        <div class="card px-4 py-3.5">
            <dt class="text-xs text-slate-500">Hari berturut-turut</dt>
            <dd class="mt-1 text-xl font-semibold tracking-tight text-slate-900 tabular">
                {{ $streakDays }}<span class="text-base font-normal text-slate-400"> hari</span>
            </dd>
        </div>
    </dl>
</section>

{{-- Packages --}}
<section class="mb-9">
    <x-page-header
        title="Paket latihan"
        description="Setiap paket dikerjakan satu kali per hari." />

    @if($packages->isEmpty())
        <div class="card">
            <x-empty-state
                icon="clipboard-list"
                title="Belum ada paket latihan"
                description="Paket latihan belum disiapkan di panel pengelolaan." />
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach($packages as $package)
                @php
                    $tryout = $package['tryout'];
                    $mastery = $package['mastery'];
                    $status = $package['status'];
                @endphp

                <article class="card flex flex-col p-4 sm:p-5">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="badge badge-brand">{{ $tryout->subject }}</span>

                        @if($status === 'done_today')
                            <span class="badge badge-success ml-auto">
                                <x-icon name="check" class="size-3" />
                                Selesai hari ini
                            </span>
                        @elseif($status === 'in_progress')
                            <span class="badge badge-warning ml-auto">Sedang berjalan</span>
                        @elseif($status === 'all_mastered')
                            <span class="badge badge-success ml-auto">Tuntas semua</span>
                        @endif
                    </div>

                    <h3 class="text-base font-semibold leading-snug text-slate-900">{{ $tryout->title }}</h3>

                    {{-- Mastery progress --}}
                    <div class="mt-4">
                        <div class="flex items-end justify-between gap-3">
                            <span class="text-xs text-slate-500">Soal dikuasai</span>
                            <span class="text-sm font-medium text-slate-900 tabular">
                                {{ $mastery['mastered'] }}<span class="text-slate-400">/{{ $mastery['total'] }}</span>
                            </span>
                        </div>

                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500 transition-[width]"
                                style="width: {{ $mastery['percentage'] }}%"></div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span>Masih dilatih: <span class="font-medium text-slate-700 tabular">{{ $mastery['learning'] }}</span></span>
                            <span>Belum dibuka: <span class="font-medium text-slate-700 tabular">{{ $mastery['untouched'] }}</span></span>
                        </div>
                    </div>

                    <dl class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 pt-4 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <x-icon name="help-circle" class="size-4 text-slate-400" />
                            <dt class="sr-only">Soal per sesi</dt>
                            <dd><span class="font-medium tabular">{{ $tryout->questions_per_session }}</span> soal/hari</dd>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-icon name="clock" class="size-4 text-slate-400" />
                            <dt class="sr-only">Durasi</dt>
                            <dd><span class="font-medium tabular">{{ $tryout->duration_minutes }}</span> menit</dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        @if($status === 'in_progress')
                            <a href="{{ route('student.exam.room', $package['session']) }}" class="btn btn-primary w-full">
                                Lanjutkan sesi
                                <x-icon name="arrow-right" class="size-4" />
                            </a>
                        @elseif($status === 'done_today')
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <a href="{{ route('student.exam.review', $package['session']) }}" class="btn btn-secondary justify-center sm:flex-1">
                                    <x-icon name="book-open" class="size-4" />
                                    Lihat pembahasan
                                </a>
                                <a href="{{ route('student.exam.result', $package['session']) }}" class="btn btn-secondary justify-center">
                                    Nilai: {{ round($package['session']->percentage) }}%
                                </a>
                            </div>
                        @elseif($status === 'all_mastered')
                            <button type="button" class="btn btn-secondary w-full" disabled>
                                <x-icon name="check-double" class="size-4" />
                                Semua soal sudah tuntas
                            </button>
                        @elseif($status === 'empty')
                            <button type="button" class="btn btn-secondary w-full" disabled>Belum ada soal</button>
                        @else
                            <form action="{{ route('student.exam.start', $tryout) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full">
                                    Mulai latihan &middot; {{ $package['due_count'] }} soal
                                    <x-icon name="arrow-right" class="size-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

{{-- Session history --}}
<section id="riwayat" class="scroll-mt-6">
    <x-page-header
        title="Riwayat sesi"
        description="Semua sesi yang sudah kamu selesaikan, terbaru lebih dulu." />

    <div class="card overflow-hidden">
        @if($recentSessions->isEmpty())
            <x-empty-state
                icon="file-text"
                title="Belum ada sesi selesai"
                description="Mulai satu paket latihan di atas untuk melihat riwayatnya di sini." />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($recentSessions as $session)
                    <li class="flex flex-wrap items-center gap-x-4 gap-y-3 p-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900">{{ $session->tryout->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $session->session_date?->translatedFormat('d M Y') ?? $session->completed_at?->translatedFormat('d M Y') }}
                                &middot; {{ $session->answers_count }} soal
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-base font-semibold text-slate-900 tabular">{{ round($session->percentage) }}%</span>

                            <a href="{{ route('student.exam.review', $session) }}" class="btn btn-secondary btn-sm">
                                Pembahasan
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($recentSessions->hasPages())
            <div class="pagination-wrap border-t border-slate-200 px-4 py-3">
                {{ $recentSessions->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
