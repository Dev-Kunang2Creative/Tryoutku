@extends('layouts.admin')

@section('title', 'Riwayat Sesi')
@section('header_title', 'Riwayat Sesi')

@section('content')
<x-page-header
    title="Riwayat sesi latihan"
    description="Catatan setiap sesi harian yang sudah diselesaikan.">
    <x-slot:actions>
        <form action="{{ route('admin.results.index') }}" method="GET" class="flex w-full items-center gap-2 sm:w-auto">
            <label for="tryout_id" class="sr-only">Filter paket soal</label>
            <select id="tryout_id" name="tryout_id" class="form-select w-full sm:w-56" onchange="this.form.submit()">
                <option value="">Semua paket</option>
                @foreach($tryouts as $tryoutOption)
                    <option value="{{ $tryoutOption->id }}" @selected(request('tryout_id') == $tryoutOption->id)>
                        {{ $tryoutOption->subject }}
                    </option>
                @endforeach
            </select>

            @if(request()->filled('tryout_id'))
                <a href="{{ route('admin.results.index') }}" class="btn btn-secondary btn-sm shrink-0">Reset</a>
            @endif
        </form>
    </x-slot:actions>
</x-page-header>

<div class="card overflow-hidden">
    @if($results->isEmpty())
        <x-empty-state
            icon="chart"
            title="Belum ada sesi selesai"
            description="Riwayat akan terisi setelah sesi latihan harian diselesaikan." />
    @else
        {{-- Table on wider screens --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Paket</th>
                        <th>Soal</th>
                        <th>Benar</th>
                        <th>Nilai</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                        @php $breakdown = $result->answerBreakdown(); @endphp
                        <tr>
                            <td class="whitespace-nowrap">
                                <div class="font-medium text-slate-900">
                                    {{ $result->session_date?->translatedFormat('d M Y') ?? '—' }}
                                </div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    Selesai {{ $result->completed_at?->format('H:i') ?? '—' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-brand">{{ $result->tryout->subject }}</span>
                            </td>
                            <td class="whitespace-nowrap text-slate-600 tabular">{{ $breakdown['total'] }}</td>
                            <td class="whitespace-nowrap text-slate-600">
                                <span class="tabular text-emerald-700">{{ $breakdown['correct'] }}</span>
                                <span class="text-xs text-slate-400">
                                    / salah <span class="tabular">{{ $breakdown['wrong'] }}</span>
                                    / kosong <span class="tabular">{{ $breakdown['empty'] }}</span>
                                </span>
                            </td>
                            <td class="whitespace-nowrap font-medium text-slate-900 tabular">{{ round($result->percentage) }}%</td>
                            <td class="text-right">
                                <a href="{{ route('admin.results.show', $result) }}" class="btn btn-secondary btn-sm">
                                    <x-icon name="eye" class="size-4" />
                                    Pembahasan
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Stacked cards on phones --}}
        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach($results as $result)
                @php $breakdown = $result->answerBreakdown(); @endphp
                <li>
                    <a href="{{ route('admin.results.show', $result) }}" class="flex items-start gap-3 p-4 transition-colors hover:bg-slate-50">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900">
                                {{ $result->session_date?->translatedFormat('d M Y') ?? '—' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">{{ $result->tryout->subject }}</p>

                            <p class="mt-2 text-xs text-slate-500">
                                <span class="tabular text-emerald-700">{{ $breakdown['correct'] }} benar</span> &middot;
                                <span class="tabular">{{ $breakdown['wrong'] }} salah</span> &middot;
                                <span class="tabular">{{ $breakdown['empty'] }} kosong</span>
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <span class="text-base font-semibold text-slate-900 tabular">{{ round($result->percentage) }}%</span>
                            <x-icon name="chevron-right" class="size-4 text-slate-300" />
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    @if($results->hasPages())
        <div class="pagination-wrap border-t border-slate-200 px-4 py-3">
            {{ $results->links() }}
        </div>
    @endif
</div>
@endsection
