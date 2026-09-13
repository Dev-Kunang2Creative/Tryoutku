@extends('layouts.admin')

@section('title', 'Edit Paket Latihan')
@section('header_title', 'Edit Paket Latihan')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.tryouts.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
            <x-icon name="arrow-left" class="size-4" />
            Kembali ke daftar paket
        </a>

        <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary btn-sm">
            <x-icon name="clipboard-list" class="size-4" />
            Bank soal ({{ $tryout->questions()->count() }})
        </a>
    </div>

    <form action="{{ route('admin.tryouts.update', $tryout) }}" method="POST" class="card card-pad">
        @csrf
        @method('PUT')

        <header class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Pengaturan paket latihan</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Perubahan berlaku untuk sesi yang dimulai setelah pengaturan disimpan.
            </p>
        </header>

        @include('admin.tryouts.form')

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.tryouts.index') }}" class="btn btn-secondary justify-center">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
        </div>
    </form>
</div>
@endsection
