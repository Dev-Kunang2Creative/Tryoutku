@props([
    'series',
    'suffix' => '',
    'yMax' => null,
    'height' => 220,
])

@php
    /**
     * Grafik garis dua deret dalam SVG murni: tanpa pustaka, tanpa permintaan
     * jaringan, dan tetap terbaca saat dicetak.
     *
     * Sumbu tanggalnya dibagi rata menurut urutan titik, bukan menurut jarak
     * hari sesungguhnya. Untuk latihan harian yang kadang bolong, cara ini
     * membuat jeda tidak menjajah lebar grafik.
     *
     * @var list<array{nama: string, warna: string, titik: list<array{tanggal: string, nilai: float|int}>}> $series
     */
    $idGrafik = 'grafik-'.\Illuminate\Support\Str::random(6);

    $semuaTanggal = collect($series)
        ->flatMap(fn ($deret) => collect($deret['titik'])->pluck('tanggal'))
        ->unique()->sort()->values();

    $jumlahTitik = $semuaTanggal->count();

    $nilaiTertinggi = collect($series)
        ->flatMap(fn ($deret) => collect($deret['titik'])->pluck('nilai'))
        ->max() ?? 0;

    $puncak = $yMax ?? max(1, (int) ceil($nilaiTertinggi / 5) * 5);

    // Ruang kiri disediakan untuk label sumbu, bawah untuk tanggal.
    $lebar = 720;
    $tinggi = (int) $height;
    $padKiri = 38;
    $padKanan = 12;
    $padAtas = 12;
    $padBawah = 28;

    $lebarPlot = $lebar - $padKiri - $padKanan;
    $tinggiPlot = $tinggi - $padAtas - $padBawah;

    $x = fn (int $indeks) => $jumlahTitik <= 1
        ? $padKiri + $lebarPlot / 2
        : $padKiri + ($indeks / ($jumlahTitik - 1)) * $lebarPlot;

    $y = fn (float $nilai) => $padAtas + $tinggiPlot - ($puncak > 0 ? ($nilai / $puncak) * $tinggiPlot : 0);

    $garisSumbu = [0, 0.25, 0.5, 0.75, 1];

    // Tanggal yang dicetak di sumbu dijarangkan agar tidak saling tabrak.
    $lompatan = max(1, (int) ceil($jumlahTitik / 6));

    $dataHover = $semuaTanggal->map(function ($tanggal) use ($series) {
        return [
            'tanggal' => \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y'),
            'nilai' => collect($series)->map(function ($deret) use ($tanggal) {
                $cocok = collect($deret['titik'])->firstWhere('tanggal', $tanggal);

                return ['nama' => $deret['nama'], 'warna' => $deret['warna'], 'nilai' => $cocok['nilai'] ?? null];
            })->values(),
        ];
    });
@endphp

@if($jumlahTitik === 0)
    <p class="px-4 py-10 text-center text-sm text-slate-500">Belum ada data untuk digambarkan.</p>
@else
    <div class="relative" data-grafik="{{ $idGrafik }}">
        <svg viewBox="0 0 {{ $lebar }} {{ $tinggi }}" class="w-full" style="height: {{ $tinggi }}px"
            role="img" aria-label="{{ collect($series)->pluck('nama')->join(' dan ') }} sepanjang waktu">

            {{-- Garis bantu dan label sumbu: tipis, abu, tidak menuntut perhatian --}}
            @foreach($garisSumbu as $bagian)
                @php $posisiY = $padAtas + $tinggiPlot - ($bagian * $tinggiPlot); @endphp
                <line x1="{{ $padKiri }}" y1="{{ $posisiY }}" x2="{{ $lebar - $padKanan }}" y2="{{ $posisiY }}"
                    stroke="#e2e8f0" stroke-width="1" />
                <text x="{{ $padKiri - 8 }}" y="{{ $posisiY + 4 }}" text-anchor="end"
                    font-size="11" fill="#94a3b8">{{ round($puncak * $bagian) }}{{ $suffix }}</text>
            @endforeach

            @foreach($semuaTanggal as $indeks => $tanggal)
                @if($indeks % $lompatan === 0 || $indeks === $jumlahTitik - 1)
                    <text x="{{ $x($indeks) }}" y="{{ $tinggi - 8 }}" text-anchor="middle"
                        font-size="11" fill="#94a3b8">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M') }}</text>
                @endif
            @endforeach

            {{-- Penanda silang mengikuti kursor --}}
            <line data-silang x1="0" y1="{{ $padAtas }}" x2="0" y2="{{ $padAtas + $tinggiPlot }}"
                stroke="#64748b" stroke-width="1" stroke-dasharray="3 3" opacity="0" />

            @foreach($series as $deret)
                @php
                    $titikTergambar = collect($deret['titik'])
                        ->map(fn ($t) => [
                            'x' => $x($semuaTanggal->search($t['tanggal'])),
                            'y' => $y((float) $t['nilai']),
                        ]);
                @endphp

                @if($titikTergambar->count() > 1)
                    <polyline points="{{ $titikTergambar->map(fn ($t) => $t['x'].','.$t['y'])->join(' ') }}"
                        fill="none" stroke="{{ $deret['warna'] }}" stroke-width="2"
                        stroke-linejoin="round" stroke-linecap="round" />
                @endif

                {{-- Cincin seukuran permukaan agar titik yang bertumpuk tetap terpisah --}}
                @foreach($titikTergambar as $t)
                    <circle cx="{{ $t['x'] }}" cy="{{ $t['y'] }}" r="4"
                        fill="{{ $deret['warna'] }}" stroke="#ffffff" stroke-width="2" />
                @endforeach
            @endforeach

            {{-- Sasaran tunjuk selebar satu kolom, jauh lebih besar dari markanya --}}
            @foreach($semuaTanggal as $indeks => $tanggal)
                <rect data-kolom="{{ $indeks }}" x="{{ $x($indeks) - ($lebarPlot / max(1, $jumlahTitik)) / 2 }}"
                    y="{{ $padAtas }}" width="{{ $lebarPlot / max(1, $jumlahTitik) }}" height="{{ $tinggiPlot }}"
                    fill="transparent" tabindex="0" role="button"
                    aria-label="{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}" />
            @endforeach
        </svg>

        <div data-tooltip
            class="pointer-events-none absolute z-10 hidden min-w-36 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-lg">
        </div>
    </div>

    <script type="application/json" data-grafik-data="{{ $idGrafik }}">@json($dataHover)</script>
@endif
