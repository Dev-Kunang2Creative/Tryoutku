@extends('layouts.admin')

@section('title', 'Paket Latihan')
@section('header_title', 'Paket Latihan')

@section('content')
<x-page-header
    title="Daftar paket soal"
    description="Maksimal 2 paket. Atur durasi, jumlah soal harian, dan bank soalnya.">
    <x-slot:actions>
        @if($tryouts->total() < \App\Models\Tryout::MAX_PACKAGES)
            <a href="{{ route('admin.tryouts.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="size-4" />
                Buat paket
            </a>
        @else
            <span class="badge badge-neutral">Batas 2 paket tercapai</span>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="card overflow-hidden">
    @if($tryouts->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            title="Belum ada paket latihan"
            description="Buat paket pertama, lalu tambahkan butir soal pilihan ganda ke dalamnya.">
            <x-slot:action>
                <a href="{{ route('admin.tryouts.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="size-4" />
                    Buat paket latihan
                </a>
            </x-slot:action>
        </x-empty-state>
    @else
        {{-- Table on wider screens --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Paket latihan</th>
                        <th>Bidang</th>
                        <th>Durasi</th>
                        <th>Soal</th>
                        <th>Per hari</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tryouts as $tryout)
                        <tr>
                            <td>
                                <div class="max-w-xs truncate font-medium text-slate-900">{{ $tryout->title }}</div>
                                <div class="mt-0.5 max-w-xs truncate text-xs text-slate-500">
                                    {{ $tryout->description ?: 'Tanpa deskripsi' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-brand">{{ $tryout->subject }}</span>
                                <div class="mt-1 text-xs text-slate-500">{{ $tryout->session_type }}</div>
                            </td>
                            <td class="whitespace-nowrap text-slate-600">
                                <span class="tabular">{{ $tryout->duration_minutes }}</span> mnt
                            </td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="link">
                                    <span class="tabular">{{ $tryout->questions_count }}</span> soal
                                </a>
                            </td>
                            <td class="whitespace-nowrap text-slate-600">
                                <span class="font-medium tabular">{{ $tryout->questions_per_session }}</span> soal
                            </td>
                            <td>
                                <span class="badge {{ $tryout->is_active ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $tryout->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary btn-sm">Bank soal</a>

                                    <a href="{{ route('admin.tryouts.edit', $tryout) }}" class="btn btn-secondary btn-sm px-2" title="Edit paket">
                                        <x-icon name="pencil" class="size-4" />
                                        <span class="sr-only">Edit</span>
                                    </a>

                                    <form action="{{ route('admin.tryouts.destroy', $tryout) }}" method="POST"
                                        onsubmit="return confirm('Hapus paket ini beserta seluruh soal dan riwayat ujiannya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm px-2" title="Hapus paket">
                                            <x-icon name="trash" class="size-4" />
                                            <span class="sr-only">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Stacked cards below lg --}}
        <ul class="divide-y divide-slate-100 lg:hidden">
            @foreach($tryouts as $tryout)
                <li class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium leading-snug text-slate-900">{{ $tryout->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $tryout->subject }} &middot; {{ $tryout->session_type }}</p>
                        </div>

                        <span class="badge shrink-0 {{ $tryout->is_active ? 'badge-success' : 'badge-neutral' }}">
                            {{ $tryout->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <dl class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <x-icon name="clock" class="size-3.5 text-slate-400" />
                            <dt class="sr-only">Durasi</dt>
                            <dd><span class="tabular">{{ $tryout->duration_minutes }}</span> menit</dd>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-icon name="help-circle" class="size-3.5 text-slate-400" />
                            <dt class="sr-only">Jumlah soal</dt>
                            <dd><span class="tabular">{{ $tryout->questions_count }}</span> soal</dd>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-icon name="calendar" class="size-3.5 text-slate-400" />
                            <dt class="sr-only">Soal per hari</dt>
                            <dd><span class="tabular">{{ $tryout->questions_per_session }}</span> soal/hari</dd>
                        </div>
                    </dl>

                    <div class="mt-3.5 flex items-center gap-2">
                        <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary btn-sm flex-1 justify-center">
                            Bank soal
                        </a>
                        <a href="{{ route('admin.tryouts.edit', $tryout) }}" class="btn btn-secondary btn-sm px-2.5">
                            <x-icon name="pencil" class="size-4" />
                            <span class="sr-only">Edit paket</span>
                        </a>
                        <form action="{{ route('admin.tryouts.destroy', $tryout) }}" method="POST"
                            onsubmit="return confirm('Hapus paket ini beserta seluruh soal dan riwayat ujiannya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm px-2.5">
                                <x-icon name="trash" class="size-4" />
                                <span class="sr-only">Hapus paket</span>
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if($tryouts->hasPages())
        <div class="pagination-wrap border-t border-slate-200 px-4 py-3">
            {{ $tryouts->links() }}
        </div>
    @endif
</div>
@endsection
