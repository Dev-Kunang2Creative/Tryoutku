@extends('layouts.admin')

@section('title', 'Bank Soal')
@section('header_title', 'Bank Soal')

@section('content')
<a href="{{ route('admin.tryouts.index') }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
    <x-icon name="arrow-left" class="size-4" />
    Kembali ke daftar paket
</a>

{{-- Package summary --}}
<div class="card card-pad mb-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base font-semibold leading-snug text-slate-900 sm:text-lg">{{ $tryout->title }}</h2>
                <span class="badge badge-brand">{{ $tryout->subject }}</span>
                @unless($tryout->is_active)
                    <span class="badge badge-neutral">Nonaktif</span>
                @endunless
            </div>

            <dl class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-600">
                <div class="flex items-center gap-1.5">
                    <x-icon name="clock" class="size-4 text-slate-400" />
                    <dt class="sr-only">Durasi</dt>
                    <dd><span class="tabular">{{ $tryout->duration_minutes }}</span> menit</dd>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-icon name="calendar" class="size-4 text-slate-400" />
                    <dt class="sr-only">Soal per sesi harian</dt>
                    <dd><span class="tabular">{{ $tryout->questions_per_session }}</span> soal/hari</dd>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-icon name="help-circle" class="size-4 text-slate-400" />
                    <dt class="sr-only">Total soal</dt>
                    <dd><span class="tabular">{{ $totalQuestions }}</span> soal di bank</dd>
                </div>
            </dl>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('admin.tryouts.edit', $tryout) }}" class="btn btn-secondary btn-sm">
                <x-icon name="pencil" class="size-4" />
                Pengaturan
            </a>
            <a href="{{ route('admin.tryouts.questions.import.create', $tryout) }}" class="btn btn-secondary btn-sm">
                <x-icon name="upload" class="size-4" />
                Impor Excel
            </a>
            <a href="{{ route('admin.tryouts.questions.create', $tryout) }}" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="size-4" />
                Tambah soal
            </a>
        </div>
    </div>
</div>

@if($totalQuestions > 0)
    {{-- Search --}}
    <form action="{{ route('admin.tryouts.questions.index', $tryout) }}" method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <label for="q" class="sr-only">Cari soal</label>
        <input type="search" id="q" name="q" value="{{ $keyword }}"
            placeholder="Cari kata dalam soal, pilihan jawaban, atau pembahasan"
            class="form-input min-w-0 flex-1 sm:max-w-md">

        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>

        @if($keyword !== '')
            <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>

    <p class="mb-3 text-xs text-slate-500">
        @if($keyword !== '')
            <span class="tabular">{{ $questions->total() }}</span> soal cocok dengan &ldquo;{{ $keyword }}&rdquo;.
        @else
            Menampilkan soal <span class="tabular">{{ $questions->firstItem() ?? 0 }}&ndash;{{ $questions->lastItem() ?? 0 }}</span>
            dari <span class="tabular">{{ $questions->total() }}</span>. Klik satu baris untuk melihat pilihan jawaban dan pembahasannya.
        @endif
    </p>
@endif

<div class="card overflow-hidden">
    @if($totalQuestions === 0)
        <x-empty-state
            icon="help-circle"
            title="Belum ada soal di paket ini"
            description="Tambahkan butir soal pilihan ganda A&ndash;E beserta kunci dan pembahasannya.">
            <x-slot:action>
                <a href="{{ route('admin.tryouts.questions.create', $tryout) }}" class="btn btn-primary">
                    <x-icon name="plus" class="size-4" />
                    Tambah soal pertama
                </a>
                <a href="{{ route('admin.tryouts.questions.import.create', $tryout) }}" class="btn btn-secondary">
                    <x-icon name="upload" class="size-4" />
                    Impor dari Excel
                </a>
            </x-slot:action>
        </x-empty-state>
    @elseif($questions->isEmpty())
        <x-empty-state
            icon="help-circle"
            title="Tidak ada soal yang cocok"
            description="Coba kata kunci lain, atau hapus pencarian untuk melihat seluruh bank soal.">
            <x-slot:action>
                <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary">
                    Tampilkan semua soal
                </a>
            </x-slot:action>
        </x-empty-state>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach($questions as $index => $question)
                @php
                    $number = $questions->firstItem() + $index;
                    $correct = $question->options->firstWhere('is_correct', true);
                @endphp

                <li class="flex items-start gap-2 p-3 sm:gap-3 sm:p-4">
                    <details class="group min-w-0 flex-1">
                        <summary class="flex cursor-pointer list-none items-start gap-3 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-md bg-slate-100 text-xs font-semibold text-slate-700 tabular">
                                {{ $number }}
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block text-sm leading-relaxed text-slate-800 group-open:font-medium">
                                    {{ \Illuminate\Support\Str::limit($question->question_text, 120) }}
                                </span>
                                <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1">
                                        Kunci
                                        <span class="font-semibold text-emerald-700">{{ $correct?->option_key ?? '—' }}</span>
                                    </span>
                                    <span>Bobot <span class="tabular">{{ $question->score_weight }}</span></span>
                                    @if($question->imageUrl())
                                        <span class="inline-flex items-center gap-1 text-slate-500">
                                            <x-icon name="image" class="size-3.5" />
                                            Bergambar
                                        </span>
                                    @endif
                                    @unless($question->explanation)
                                        <span class="badge badge-neutral">Tanpa pembahasan</span>
                                    @endunless
                                </span>
                            </span>

                            <x-icon name="chevron-down" class="mt-0.5 size-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180" />
                        </summary>

                        <div class="mt-4 pl-0 sm:pl-10">
                            <div class="whitespace-pre-line text-[0.9375rem] leading-relaxed text-slate-800">{{ $question->question_text }}</div>
                            @if($question->imageUrl())
                                <figure class="mt-4">
                                    <img src="{{ $question->imageUrl() }}" alt="Gambar pendukung soal"
                                        class="max-h-80 w-auto max-w-full rounded-lg border border-slate-200 bg-white">
                                </figure>
                            @endif

                            <ul class="mt-4 space-y-2">
                                @foreach($question->options as $option)
                                    <li class="flex items-start gap-3 rounded-lg border px-3 py-2.5 {{ $option->is_correct ? 'border-emerald-300 bg-emerald-50/60' : 'border-slate-200' }}">
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded text-xs font-semibold {{ $option->is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $option->option_key }}
                                        </span>

                                        <span class="min-w-0 flex-1 text-sm leading-relaxed text-slate-700">{{ $option->option_text }}</span>

                                        @if($option->is_correct)
                                            <span class="badge badge-success shrink-0">
                                                <x-icon name="check" class="size-3" />
                                                Kunci
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if($question->explanation)
                                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3.5">
                                    <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <x-icon name="lightbulb" class="size-4" />
                                        Pembahasan
                                    </p>
                                    <div class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $question->explanation }}</div>
                                </div>
                            @endif
                        </div>
                    </details>

                    <div class="flex shrink-0 items-center gap-1.5">
                        <a href="{{ route('admin.tryouts.questions.edit', [$tryout, $question]) }}" class="btn btn-secondary btn-sm px-2" title="Edit soal">
                            <x-icon name="pencil" class="size-4" />
                            <span class="sr-only">Edit soal nomor {{ $number }}</span>
                        </a>

                        <form action="{{ route('admin.tryouts.questions.destroy', [$tryout, $question]) }}" method="POST"
                            onsubmit="return confirm('Hapus butir soal nomor {{ $number }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm px-2" title="Hapus soal">
                                <x-icon name="trash" class="size-4" />
                                <span class="sr-only">Hapus soal nomor {{ $number }}</span>
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        @if($questions->hasPages())
            <div class="pagination-wrap border-t border-slate-200 px-4 py-3">
                {{ $questions->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
