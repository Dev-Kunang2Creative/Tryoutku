@extends('layouts.admin')

@section('title', 'Impor Soal')
@section('header_title', 'Impor Soal dari Excel')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
        <x-icon name="arrow-left" class="size-4" />
        Kembali ke bank soal
    </a>

    {{-- Step 1: template --}}
    <section class="card card-pad mb-5">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-slate-900">1. Unduh template</h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                    Template sudah berisi header kolom, satu baris contoh, dan lembar petunjuk.
                    Kolom kunci jawaban memakai daftar pilihan A&ndash;E supaya tidak salah ketik.
                </p>
            </div>

            <a href="{{ route('admin.tryouts.questions.import.template', $tryout) }}" class="btn btn-secondary btn-sm shrink-0">
                <x-icon name="download" class="size-4" />
                Unduh template
            </a>
        </header>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[36rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th scope="col" class="py-2 pr-4 font-medium text-slate-600">Kolom</th>
                        <th scope="col" class="py-2 pr-4 font-medium text-slate-600">Wajib</th>
                        <th scope="col" class="py-2 font-medium text-slate-600">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $notes = [
                            'question_text' => 'Teks pertanyaan.',
                            'option_a' => 'Teks pilihan A.',
                            'option_b' => 'Teks pilihan B.',
                            'option_c' => 'Teks pilihan C.',
                            'option_d' => 'Teks pilihan D.',
                            'option_e' => 'Teks pilihan E.',
                            'correct_option' => 'Satu huruf: A, B, C, D, atau E.',
                            'explanation' => 'Boleh kosong.',
                            'score_weight' => 'Boleh kosong, dianggap 10 poin.',
                            'order' => 'Boleh kosong, ditaruh di urutan berikutnya.',
                            'image' => 'Tempel gambar di sel ini. Boleh kosong.',
                        ];
                    @endphp

                    @foreach($columns as $field => $label)
                        <tr>
                            <td class="py-2 pr-4 align-top font-medium text-slate-800">{{ $label }}</td>
                            <td class="py-2 pr-4 align-top">
                                @if(in_array($field, $requiredColumns, true))
                                    <span class="badge badge-brand">Wajib</span>
                                @else
                                    <span class="badge badge-neutral">Opsional</span>
                                @endif
                            </td>
                            <td class="py-2 align-top text-slate-600">{{ $notes[$field] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Step 2: upload --}}
    <form action="{{ route('admin.tryouts.questions.import.store', $tryout) }}" method="POST"
        enctype="multipart/form-data" class="card card-pad">
        @csrf

        <header class="mb-5 border-b border-slate-100 pb-5">
            <h2 class="text-base font-semibold text-slate-900">2. Unggah berkas terisi</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Soal akan ditambahkan ke paket
                <span class="font-medium text-slate-700">{{ $tryout->title }}</span>.
                Soal yang sudah ada tidak dihapus atau diubah.
            </p>
        </header>

        @if(session('import_errors'))
            <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3.5">
                <p class="flex items-center gap-2 text-sm font-semibold text-red-900">
                    <x-icon name="alert-triangle" class="size-4 shrink-0 text-red-600" />
                    Berkas ditolak, tidak ada soal yang tersimpan
                </p>
                <ul class="mt-2.5 space-y-1.5 text-sm leading-relaxed text-red-800">
                    @foreach(session('import_errors') as $error)
                        <li class="flex gap-2">
                            <span aria-hidden="true" class="text-red-400">&bull;</span>
                            <span class="min-w-0 flex-1">{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label for="file" class="form-label">Berkas Excel</label>
            <input type="file" id="file" name="file" required accept=".xlsx,.xls"
                class="block w-full cursor-pointer rounded-lg border border-slate-300 text-sm text-slate-600 transition-colors file:mr-4 file:cursor-pointer file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 hover:border-slate-400 hover:file:bg-slate-200">
            <x-input-error for="file" />
            <p class="mt-2 text-xs leading-relaxed text-slate-500">
                Format .xlsx atau .xls, maksimal 5 MB dan {{ $maxRows }} baris soal.
                Bila ada satu baris yang salah, seluruh berkas dibatalkan supaya bank soal tidak terisi separuh.
            </p>
        </div>

        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3.5">
            <p class="flex items-center gap-2 text-sm font-semibold text-amber-900">
                <x-icon name="alert-triangle" class="size-4 shrink-0 text-amber-600" />
                Kalau soalnya memakai gambar
            </p>
            <p class="mt-2 text-sm leading-relaxed text-amber-900">
                Di Excel, gambar mengambang di atas lembar dan tidak benar-benar berada di dalam sel.
                Yang dipakai adalah baris tempat <strong>sudut kiri atas</strong> gambar berada.
                Setelah menempel, seret gambarnya sampai sudut kiri atas itu jelas berada di dalam baris soal
                yang benar. Gambar yang menempel di baris tanpa soal akan menggagalkan seluruh berkas
                berikut nomor barisnya, supaya tidak ada gambar yang diam-diam tertukar.
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.tryouts.questions.index', $tryout) }}" class="btn btn-secondary justify-center">Batal</a>
            <button type="submit" class="btn btn-primary">
                <x-icon name="upload" class="size-4" />
                Unggah dan simpan
            </button>
        </div>
    </form>
</div>
@endsection
