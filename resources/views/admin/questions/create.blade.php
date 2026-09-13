@extends('layouts.admin')

@section('title', 'Tambah Soal')
@section('header_title', 'Tambah Butir Soal')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
        <x-icon name="arrow-left" class="size-4" />
        Kembali ke bank soal
    </a>

    <form action="{{ route('admin.tryouts.questions.store', $tryout) }}" method="POST" enctype="multipart/form-data" class="card card-pad">
        @csrf

        <header class="mb-6 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">Butir soal baru</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Paket <span class="font-medium text-slate-700">{{ $tryout->title }}</span>
            </p>
        </header>

        @include('admin.questions.form')

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary justify-center">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan butir soal</button>
        </div>
    </form>
</div>
@endsection
