@extends('layouts.admin')

@section('title', 'Pembahasan Sesi')
@section('header_title', 'Pembahasan Sesi')

@section('content')
<div class="pb-20 lg:pb-0">
    <a href="{{ route('admin.results.index') }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
        <x-icon name="arrow-left" class="size-4" />
        Kembali ke riwayat sesi
    </a>

    {{-- Session summary --}}
    <div class="card card-pad mb-6">
        <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="min-w-0">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Paket</dt>
                <dd class="mt-1.5 text-sm font-medium leading-snug text-slate-900">{{ $attempt->tryout->title }}</dd>
                <dd class="mt-0.5 text-xs text-slate-500">{{ $attempt->tryout->subject }}</dd>
            </div>

            <div class="sm:border-l sm:border-slate-100 sm:pl-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal sesi</dt>
                <dd class="mt-1.5 text-sm font-medium text-slate-900">
                    {{ $attempt->session_date?->translatedFormat('d F Y') ?? '—' }}
                </dd>
                <dd class="mt-0.5 text-xs text-slate-500">
                    Selesai {{ $attempt->completed_at?->format('H:i') ?? '—' }}
                </dd>
            </div>

            <div class="lg:border-l lg:border-slate-100 lg:pl-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Nilai sesi</dt>
                <dd class="mt-1.5 text-2xl font-semibold tracking-tight text-slate-900 tabular">
                    {{ round($attempt->percentage) }}%
                </dd>
                <dd class="mt-0.5 text-xs text-slate-500 tabular">
                    {{ $breakdown['correct'] }} dari {{ $breakdown['total'] }} soal benar
                </dd>
            </div>

            <div class="sm:border-l sm:border-slate-100 sm:pl-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Rincian</dt>
                <dd class="mt-1.5 flex flex-wrap gap-1.5">
                    <span class="badge badge-success tabular">{{ $breakdown['correct'] }} benar</span>
                    <span class="badge badge-danger tabular">{{ $breakdown['wrong'] }} salah</span>
                    <span class="badge badge-neutral tabular">{{ $breakdown['empty'] }} kosong</span>
                </dd>
                <dd class="mt-2 text-xs text-slate-500">
                    {{ $breakdown['wrong'] + $breakdown['empty'] }} soal diulang keesokan hari
                </dd>
            </div>
        </dl>
    </div>

    <x-page-header
        title="Tinjauan butir soal"
        description="Pilih nomor soal untuk melihat jawaban anak, kunci, dan pembahasannya." />

    <x-question-explorer :answers="$answers" answer-label="Jawaban anak" />
</div>
@endsection
