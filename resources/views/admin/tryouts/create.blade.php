@extends('layouts.admin')

@section('title', 'Buat Paket Latihan')
@section('header_title', 'Buat Paket Latihan')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('admin.tryouts.index') }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
        <x-icon name="arrow-left" class="size-4" />
        Kembali ke daftar paket
    </a>

    <form action="{{ route('admin.tryouts.store') }}" method="POST" class="card card-pad">
        @csrf

        <header class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Informasi paket latihan</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Tentukan judul, durasi simulasi, dan batas kelulusan paket ini.
            </p>
        </header>

        @include('admin.tryouts.form')

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.tryouts.index') }}" class="btn btn-secondary justify-center">Batal</a>
            <button type="submit" class="btn btn-primary">
                Simpan &amp; lanjut buat soal
                <x-icon name="arrow-right" class="size-4" />
            </button>
        </div>
    </form>
</div>
@endsection
