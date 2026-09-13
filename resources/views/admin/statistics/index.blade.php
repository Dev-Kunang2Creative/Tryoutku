@extends('layouts.admin')

@section('title', 'Statistik Belajar')
@section('header_title', 'Statistik Belajar')

@php
    /**
     * Dua warna deret, satu per paket. Keduanya lolos pemeriksaan keterbacaan
     * bagi mata yang sulit membedakan warna: selisihnya jauh di atas ambang,
     * dan tiap garis tetap diberi label langsung sehingga warna tidak pernah
     * menjadi satu-satunya penanda.
     */
    $warnaDeret = ['#2456c8', '#eb6834'];

    $rupaVonis = [
        'tumbuh' => ['ikon' => 'check-circle', 'bingkai' => 'border-emerald-200 bg-emerald-50', 'aksen' => 'text-emerald-700', 'ikonWarna' => 'text-emerald-600'],
        'melambat' => ['ikon' => 'info', 'bingkai' => 'border-brand-200 bg-brand-50', 'aksen' => 'text-brand-800', 'ikonWarna' => 'text-brand-600'],
        'mandek' => ['ikon' => 'alert-triangle', 'bingkai' => 'border-amber-200 bg-amber-50', 'aksen' => 'text-amber-900', 'ikonWarna' => 'text-amber-600'],
        'jarang_latihan' => ['ikon' => 'alert-triangle', 'bingkai' => 'border-amber-200 bg-amber-50', 'aksen' => 'text-amber-900', 'ikonWarna' => 'text-amber-600'],
        'belum_cukup' => ['ikon' => 'info', 'bingkai' => 'border-slate-200 bg-slate-50', 'aksen' => 'text-slate-700', 'ikonWarna' => 'text-slate-500'],
    ][$vonis['kunci']];

    $durasiTerbaca = function (?int $detik): string {
        if ($detik === null) {
            return '—';
        }

        $menit = intdiv($detik, 60);
        $sisa = $detik % 60;

        return $menit > 0 ? $menit.' m '.$sisa.' d' : $sisa.' detik';
    };

    $selisih = function (?float $baru, ?float $lama): ?array {
        if ($baru === null || $lama === null) {
            return null;
        }

        $beda = round($baru - $lama, 1);

        return ['beda' => $beda, 'naik' => $beda > 0, 'datar' => abs($beda) < 0.05];
    };
@endphp

@section('content')
<x-page-header
    title="Perkembangan {{ $pelajar->name }}"
    description="Disusun untuk menjawab satu hal: sedang bertumbuh atau jalan di tempat." />

{{-- Kesimpulan --}}
<div class="mb-6 rounded-xl border {{ $rupaVonis['bingkai'] }} p-5">
    <div class="flex items-start gap-3">
        <x-icon :name="$rupaVonis['ikon']" class="mt-0.5 size-5 shrink-0 {{ $rupaVonis['ikonWarna'] }}" />
        <div class="min-w-0">
            <h2 class="text-lg font-semibold {{ $rupaVonis['aksen'] }}">{{ $vonis['judul'] }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-700">{{ $vonis['alasan'] }}</p>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $vonis['saran'] }}</p>
        </div>
    </div>
</div>

{{-- Angka utama --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat
        label="Soal dikuasai"
        value="{{ $ringkasan['dikuasai'] }} / {{ $ringkasan['total_soal'] }}"
        icon="check-double"
        hint="{{ $ringkasan['persen_dikuasai'] }}% dari seluruh bank soal. {{ $ringkasan['dikuasai_pekan_ini'] }} di antaranya tuntas pekan ini." />

    @php $bedaAkurasi = $selisih($ringkasan['akurasi_baru'], $ringkasan['akurasi_lama']); @endphp
    <x-stat
        label="Akurasi rata-rata"
        value="{{ $ringkasan['akurasi'] !== null ? $ringkasan['akurasi'].'%' : '—' }}"
        icon="target"
        hint="{{ $bedaAkurasi === null
            ? 'Belum ada pembanding periode sebelumnya.'
            : ($bedaAkurasi['datar']
                ? 'Setara dengan pekan sebelumnya.'
                : ($bedaAkurasi['naik'] ? 'Naik '.abs($bedaAkurasi['beda']).' poin dari pekan sebelumnya.' : 'Turun '.abs($bedaAkurasi['beda']).' poin dari pekan sebelumnya.')) }}" />

    @php $bedaDurasi = $selisih($ringkasan['durasi_baru'], $ringkasan['durasi_lama']); @endphp
    <x-stat
        label="Lama per sesi"
        value="{{ $durasiTerbaca($ringkasan['durasi']) }}"
        icon="clock"
        hint="{{ $bedaDurasi === null
            ? 'Belum ada pembanding periode sebelumnya.'
            : ($bedaDurasi['naik'] ? 'Lebih lama dari pekan sebelumnya.' : 'Lebih cepat dari pekan sebelumnya — tanda makin lancar.') }}" />

    <x-stat
        label="Rentetan hari"
        value="{{ $ringkasan['rentetan_hari'] }} hari"
        icon="flag"
        hint="{{ $ringkasan['hari_aktif_pekan_ini'] }} hari aktif dalam sepekan terakhir, dari {{ $ringkasan['total_sesi'] }} sesi seluruhnya." />
</div>

{{-- Kurva penguasaan --}}
<section class="mb-6">
    <div class="card p-4 sm:p-5">
        <header class="mb-1">
            <h2 class="text-base font-semibold text-slate-900">Soal yang sudah dikuasai</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Menanjak berarti bertumbuh. Mendatar berarti latihan berjalan tanpa hasil baru
                &mdash; di situlah perlu ditengok soal mana yang menahan.
            </p>
        </header>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
            @foreach($kurvaPenguasaan as $i => $deret)
                <span class="flex items-center gap-2 text-sm text-slate-600">
                    <span class="inline-block h-0.5 w-4 rounded" style="background: {{ $warnaDeret[$i % 2] }}"></span>
                    {{ $deret['tryout']->subject }}
                </span>
            @endforeach
        </div>

        <div class="mt-3 overflow-x-auto">
            <x-line-chart :series="collect($kurvaPenguasaan)->map(fn ($d, $i) => [
                'nama' => $d['tryout']->subject,
                'warna' => $warnaDeret[$i % 2],
                'titik' => $d['titik'],
            ])->all()" />
        </div>
    </div>
</section>

{{-- Kurva akurasi --}}
<section class="mb-6">
    <div class="card p-4 sm:p-5">
        <header class="mb-1">
            <h2 class="text-base font-semibold text-slate-900">Ketepatan tiap sesi</h2>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Naik turun antar sesi itu wajar. Yang dibaca adalah arah umumnya sepanjang beberapa pekan.
            </p>
        </header>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
            @foreach($kurvaAkurasi as $i => $deret)
                <span class="flex items-center gap-2 text-sm text-slate-600">
                    <span class="inline-block h-0.5 w-4 rounded" style="background: {{ $warnaDeret[$i % 2] }}"></span>
                    {{ $deret['tryout']->subject }}
                </span>
            @endforeach
        </div>

        <div class="mt-3 overflow-x-auto">
            <x-line-chart suffix="%" :y-max="100" :series="collect($kurvaAkurasi)->map(fn ($d, $i) => [
                'nama' => $d['tryout']->subject,
                'warna' => $warnaDeret[$i % 2],
                'titik' => $d['titik'],
            ])->all()" />
        </div>
    </div>
</section>

{{-- Rincian per paket --}}
<section class="mb-6">
    <x-page-header title="Rincian per paket" />

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach($rincianPaket as $i => $baris)
            @php $m = $baris['penguasaan']; @endphp
            <div class="card card-pad">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-0.5 w-4 rounded" style="background: {{ $warnaDeret[$i % 2] }}"></span>
                    <h3 class="text-sm font-semibold text-slate-900">{{ $baris['tryout']->title }}</h3>
                </div>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full" style="width: {{ $m['percentage'] }}%; background: {{ $warnaDeret[$i % 2] }}"></div>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Dikuasai</dt>
                        <dd class="font-medium text-slate-900 tabular">{{ $m['mastered'] }} / {{ $m['total'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Masih dipelajari</dt>
                        <dd class="font-medium text-slate-900 tabular">{{ $m['learning'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Belum tersentuh</dt>
                        <dd class="font-medium text-slate-900 tabular">{{ $m['untouched'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Ketepatan</dt>
                        <dd class="font-medium text-slate-900 tabular">{{ $baris['akurasi'] !== null ? $baris['akurasi'].'%' : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Sesi selesai</dt>
                        <dd class="font-medium text-slate-900 tabular">{{ $baris['sesi'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Lama per sesi</dt>
                        <dd class="font-medium text-slate-900 tabular">{{ $durasiTerbaca($baris['durasi']) }}</dd>
                    </div>
                </dl>
            </div>
        @endforeach
    </div>
</section>

{{-- Soal yang menyangkut --}}
<section class="mb-6">
    <x-page-header
        title="Soal yang menyangkut"
        description="Berulang kali dijawab salah dan belum juga tuntas. Kalau kurva di atas mendatar, sebabnya biasanya ada di sini." />

    <div class="card overflow-hidden">
        @if($soalMenyangkut->isEmpty())
            <x-empty-state
                icon="check-circle"
                title="Tidak ada soal yang menyangkut"
                description="Belum ada soal yang berulang kali dijawab salah. Pertanda bagus." />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($soalMenyangkut as $progres)
                    <li class="flex flex-wrap items-start gap-x-4 gap-y-2 p-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm leading-relaxed text-slate-800">
                                {{ \Illuminate\Support\Str::limit($progres->question->question_text, 110) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $progres->question->tryout->subject }}
                                @if($progres->last_answered_on)
                                    &middot; terakhir {{ $progres->last_answered_on->translatedFormat('d M Y') }}
                                @endif
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-4 text-sm">
                            <span class="text-red-700">
                                <span class="font-semibold tabular">{{ $progres->times_wrong }}</span>
                                <span class="text-xs text-slate-500">salah</span>
                            </span>
                            <span class="text-emerald-700">
                                <span class="font-semibold tabular">{{ $progres->times_correct }}</span>
                                <span class="text-xs text-slate-500">benar</span>
                            </span>
                            <a href="{{ route('admin.tryouts.questions.edit', [$progres->question->tryout, $progres->question]) }}"
                                class="btn btn-secondary btn-sm px-2" title="Tinjau soal">
                                <x-icon name="pencil" class="size-4" />
                                <span class="sr-only">Tinjau soal ini</span>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

{{-- Tabel angka mentah --}}
<section>
    <x-page-header
        title="Angka tiap sesi"
        description="Sumber kedua grafik di atas, supaya setiap nilai tetap terbaca tanpa perlu mengarahkan kursor." />

    <div class="card overflow-hidden">
        @if($sesiTerakhir->isEmpty())
            <x-empty-state icon="chart" title="Belum ada sesi selesai"
                description="Statistik akan terisi setelah latihan harian pertama diselesaikan." />
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Paket</th>
                            <th>Benar</th>
                            <th>Salah</th>
                            <th>Ketepatan</th>
                            <th>Lama</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sesiTerakhir as $sesi)
                            @php
                                $rincian = $sesi->answerBreakdown();
                                $lama = $sesi->started_at && $sesi->completed_at
                                    ? $sesi->started_at->diffInSeconds($sesi->completed_at)
                                    : null;
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ ($sesi->session_date ?? $sesi->completed_at)->translatedFormat('d M Y') }}</td>
                                <td>{{ $sesi->tryout->subject }}</td>
                                <td class="tabular">{{ $rincian['correct'] ?? '—' }}</td>
                                <td class="tabular">{{ $rincian['wrong'] ?? '—' }}</td>
                                <td class="tabular">{{ round($sesi->percentage) }}%</td>
                                <td class="whitespace-nowrap tabular">{{ $durasiTerbaca($lama) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
