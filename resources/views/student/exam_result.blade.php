@extends('layouts.app')

@section('title', 'Hasil Sesi')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('student.dashboard') }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
        <x-icon name="arrow-left" class="size-4" />
        Kembali ke beranda
    </a>

    @php
        $percentage = round($attempt->percentage);
        $allCorrect = $breakdown['correct'] === $breakdown['total'] && $breakdown['total'] > 0;
    @endphp

    {{-- Session summary --}}
    <section class="card card-pad mb-6">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-8">
            <div class="flex shrink-0 items-center gap-4 sm:flex-col sm:gap-3">
                <div class="relative size-24 shrink-0 sm:size-28">
                    <svg viewBox="0 0 36 36" class="size-full -rotate-90" aria-hidden="true">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3" class="text-slate-100" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                            class="{{ $allCorrect ? 'text-emerald-600' : 'text-brand-600' }}"
                            stroke-dasharray="{{ round(min($percentage, 100) * 0.999, 2) }} 100" />
                    </svg>

                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-semibold tracking-tight text-slate-900 tabular sm:text-2xl">{{ $percentage }}%</span>
                        <span class="text-[0.6875rem] text-slate-500 tabular">{{ $breakdown['correct'] }}/{{ $breakdown['total'] }} benar</span>
                    </div>
                </div>
            </div>

            <div class="min-w-0 flex-1 border-t border-slate-100 pt-5 sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
                <h1 class="text-lg font-semibold leading-snug tracking-tight text-slate-900">{{ $attempt->tryout->title }}</h1>

                <p class="mt-1.5 text-sm text-slate-500">
                    Sesi {{ $attempt->session_date?->translatedFormat('d F Y') ?? $attempt->completed_at?->translatedFormat('d F Y') }}
                </p>

                <dl class="mt-5 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4 text-center">
                    <div>
                        <dd class="text-base font-semibold text-emerald-700 tabular">{{ $breakdown['correct'] }}</dd>
                        <dt class="mt-0.5 text-xs text-slate-500">Benar</dt>
                    </div>
                    <div>
                        <dd class="text-base font-semibold text-red-700 tabular">{{ $breakdown['wrong'] }}</dd>
                        <dt class="mt-0.5 text-xs text-slate-500">Salah</dt>
                    </div>
                    <div>
                        <dd class="text-base font-semibold text-slate-500 tabular">{{ $breakdown['empty'] }}</dd>
                        <dt class="mt-0.5 text-xs text-slate-500">Kosong</dt>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- What happens next --}}
    <section class="card card-pad mb-6">
        <h2 class="text-sm font-semibold text-slate-900">Jadwal pengulangan</h2>

        @php $repeatCount = $breakdown['wrong'] + $breakdown['empty']; @endphp

        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
            @if($repeatCount > 0)
                <span class="font-medium text-slate-700 tabular">{{ $repeatCount }} soal</span> akan muncul lagi besok karena belum dijawab benar.
                Soal berhenti muncul setelah dijawab benar {{ \App\Models\QuestionProgress::MASTERY_STREAK }}&times; berturut-turut.
            @else
                Semua soal di sesi ini dijawab benar. Soal akan muncul sekali lagi besok untuk memastikan sudah benar-benar dikuasai.
            @endif
        </p>

        <div class="mt-4 border-t border-slate-100 pt-4">
            <div class="flex items-end justify-between gap-3">
                <span class="text-xs text-slate-500">Total dikuasai di paket ini</span>
                <span class="text-sm font-medium text-slate-900 tabular">
                    {{ $mastery['mastered'] }}<span class="text-slate-400">/{{ $mastery['total'] }}</span>
                </span>
            </div>

            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $mastery['percentage'] }}%"></div>
            </div>
        </div>
    </section>

    <div class="flex flex-col gap-2 sm:flex-row">
        <a href="{{ route('student.exam.review', $attempt) }}" class="btn btn-primary justify-center sm:flex-1">
            <x-icon name="book-open" class="size-4" />
            Buka pembahasan
        </a>

        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary justify-center">Selesai</a>
    </div>
</div>
@endsection
