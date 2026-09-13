@extends('layouts.app')

@section('title', 'Pembahasan')

@section('content')
<div class="pb-20 lg:pb-0">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('student.exam.result', $attempt) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
            <x-icon name="arrow-left" class="size-4" />
            Kembali ke hasil sesi
        </a>

        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm">Beranda</a>
    </div>

    <x-page-header
        title="Pembahasan soal"
        description="Pilih nomor soal untuk membaca pembahasannya satu per satu.">
        <x-slot:actions>
            <span class="badge badge-neutral">{{ $attempt->tryout->subject }}</span>
            <span class="text-xs text-slate-500">
                {{ $attempt->session_date?->translatedFormat('d M Y') ?? $attempt->completed_at?->translatedFormat('d M Y') }}
            </span>
        </x-slot:actions>
    </x-page-header>

    <x-question-explorer :answers="$answers" answer-label="Jawaban kamu" />
</div>
@endsection
