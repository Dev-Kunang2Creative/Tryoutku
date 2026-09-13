<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\QuestionProgress;
use App\Models\Tryout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Potret perkembangan belajar si pelajar.
 *
 * Pertanyaan yang ingin dijawab halaman ini hanya satu: anaknya sedang
 * bertumbuh atau sedang jalan di tempat. Semua angka di sini disusun untuk
 * menjawab itu, bukan sekadar melaporkan aktivitas.
 */
class LearnerStatisticsController extends Controller
{
    /**
     * Panjang satu periode pembanding. Dua pekan terakhir diadu agar arah
     * perkembangan terlihat, bukan cuma angka mutlaknya.
     */
    private const HARI_PERIODE = 7;

    /**
     * Sebuah soal dianggap menyangkut bila sudah sekian kali dijawab salah
     * dan tetap belum dikuasai.
     */
    private const AMBANG_MENYANGKUT = 2;

    public function index()
    {
        $pelajar = User::where('role', 'user')->first() ?? Auth::user();
        $paketSoal = Tryout::withCount('questions')->orderBy('id')->get();

        $sesi = ExamAttempt::with('tryout')
            ->where('user_id', $pelajar->id)
            ->where('status', 'completed')
            ->orderBy('completed_at')
            ->get();

        $penguasaan = $this->riwayatPenguasaan($pelajar);

        // Tabel di bawah halaman memerinci benar dan salah tiap sesi, jadi
        // jawabannya diambil sekaligus alih-alih satu permintaan per baris.
        $sesiTerakhir = $sesi->sortByDesc('completed_at')->take(20)->values();
        $sesiTerakhir->load('answers');

        return view('admin.statistics.index', [
            'pelajar' => $pelajar,
            'vonis' => $this->vonis($sesi, $penguasaan),
            'ringkasan' => $this->ringkasan($sesi, $penguasaan, $paketSoal, $pelajar),
            'kurvaPenguasaan' => $this->kurvaPenguasaan($paketSoal, $penguasaan),
            'kurvaAkurasi' => $this->kurvaAkurasi($paketSoal, $sesi),
            'rincianPaket' => $this->rincianPaket($paketSoal, $sesi, $pelajar),
            'soalMenyangkut' => $this->soalMenyangkut($pelajar),
            'sesiTerakhir' => $sesiTerakhir,
        ]);
    }

    /**
     * Setiap soal yang sudah dikuasai, beserta tanggal dan paketnya.
     *
     * Pengelompokan dikerjakan di PHP, bukan lewat fungsi tanggal bawaan basis
     * data, supaya perilakunya sama antara MySQL di server dan SQLite di tes.
     *
     * @return Collection<int, array{tryout_id: int, tanggal: string}>
     */
    private function riwayatPenguasaan(User $pelajar): Collection
    {
        return QuestionProgress::query()
            ->where('question_progress.user_id', $pelajar->id)
            ->whereNotNull('question_progress.mastered_at')
            ->join('questions', 'questions.id', '=', 'question_progress.question_id')
            ->orderBy('question_progress.mastered_at')
            ->get(['questions.tryout_id', 'question_progress.mastered_at'])
            ->map(fn ($baris) => [
                'tryout_id' => (int) $baris->tryout_id,
                'tanggal' => Carbon::parse($baris->mastered_at)->toDateString(),
            ]);
    }

    /**
     * Kesimpulan yang bisa dibaca sekali lihat, lengkap dengan alasannya.
     *
     * @param  Collection<int, ExamAttempt>  $sesi
     * @param  Collection<int, array{tryout_id: int, tanggal: string}>  $penguasaan
     * @return array{kunci: string, judul: string, alasan: string, saran: string}
     */
    private function vonis(Collection $sesi, Collection $penguasaan): array
    {
        if ($sesi->count() < 4) {
            return [
                'kunci' => 'belum_cukup',
                'judul' => 'Belum cukup data',
                'alasan' => 'Baru '.$sesi->count().' sesi yang selesai. Arah perkembangan belum bisa dibaca dari sesedikit itu.',
                'saran' => 'Kumpulkan dulu sekitar seminggu latihan, lalu tengok lagi halaman ini.',
            ];
        }

        $batasBaru = Carbon::today()->subDays(self::HARI_PERIODE);
        $batasLama = Carbon::today()->subDays(self::HARI_PERIODE * 2);

        $dikuasaiBaru = $penguasaan->filter(fn ($p) => $p['tanggal'] > $batasBaru->toDateString())->count();
        $dikuasaiLama = $penguasaan->filter(
            fn ($p) => $p['tanggal'] > $batasLama->toDateString() && $p['tanggal'] <= $batasBaru->toDateString()
        )->count();

        $hariAktif = $sesi
            ->filter(fn ($s) => $s->completed_at?->greaterThan($batasBaru))
            ->map(fn ($s) => $s->completed_at->toDateString())
            ->unique()
            ->count();

        if ($hariAktif < 3) {
            return [
                'kunci' => 'jarang_latihan',
                'judul' => 'Jarang berlatih',
                'alasan' => 'Sepekan terakhir hanya ada '.$hariAktif.' hari dengan latihan yang selesai. Yang menghambat bukan kemampuannya, melainkan kekerapannya.',
                'saran' => 'Model belajar ini bersandar pada pengulangan harian. Soal yang salah dijadwalkan muncul besok, dan jadwal itu meleset kalau harinya terlewat.',
            ];
        }

        if ($dikuasaiBaru === 0) {
            return [
                'kunci' => 'mandek',
                'judul' => 'Jalan di tempat',
                'alasan' => 'Latihan berjalan '.$hariAktif.' hari sepekan ini, tetapi tidak ada satu pun soal baru yang tuntas dikuasai.',
                'saran' => 'Tengok daftar soal yang menyangkut di bawah. Biasanya ada segelintir soal yang terus berulang dan menahan lajunya.',
            ];
        }

        if ($dikuasaiLama > 0 && $dikuasaiBaru < $dikuasaiLama) {
            return [
                'kunci' => 'melambat',
                'judul' => 'Tumbuh, tapi melambat',
                'alasan' => $dikuasaiBaru.' soal dikuasai pekan ini, turun dari '.$dikuasaiLama.' soal pekan sebelumnya.',
                'saran' => 'Melambat itu wajar ketika soal yang mudah sudah habis dan tinggal yang sulit. Perhatikan apakah penurunannya berlanjut pekan depan.',
            ];
        }

        return [
            'kunci' => 'tumbuh',
            'judul' => 'Bertumbuh',
            'alasan' => $dikuasaiBaru.' soal tuntas dikuasai sepekan ini'
                .($dikuasaiLama > 0 ? ', dibanding '.$dikuasaiLama.' soal pekan sebelumnya' : '').'.',
            'saran' => 'Pertahankan kekerapannya. Kurva penguasaan di bawah memperlihatkan lajunya.',
        ];
    }

    /**
     * Angka-angka utama, masing-masing beserta pembanding periode sebelumnya
     * supaya arahnya terbaca, bukan cuma besarannya.
     *
     * @param  Collection<int, ExamAttempt>  $sesi
     * @param  Collection<int, array{tryout_id: int, tanggal: string}>  $penguasaan
     * @param  Collection<int, Tryout>  $paketSoal
     * @return array<string, mixed>
     */
    private function ringkasan(Collection $sesi, Collection $penguasaan, Collection $paketSoal, User $pelajar): array
    {
        $totalSoal = (int) $paketSoal->sum('questions_count');
        $batasBaru = Carbon::today()->subDays(self::HARI_PERIODE)->toDateString();

        $sesiBaru = $sesi->filter(fn ($s) => $s->completed_at?->toDateString() > $batasBaru);
        $sesiLama = $sesi->diff($sesiBaru);

        return [
            'total_soal' => $totalSoal,
            'dikuasai' => $penguasaan->count(),
            'persen_dikuasai' => $totalSoal > 0 ? round(($penguasaan->count() / $totalSoal) * 100, 1) : 0.0,
            'dikuasai_pekan_ini' => $penguasaan->filter(fn ($p) => $p['tanggal'] > $batasBaru)->count(),

            'total_sesi' => $sesi->count(),
            'akurasi' => $this->rataAkurasi($sesi),
            'akurasi_baru' => $this->rataAkurasi($sesiBaru),
            'akurasi_lama' => $this->rataAkurasi($sesiLama),

            'durasi' => $this->rataDurasi($sesi),
            'durasi_baru' => $this->rataDurasi($sesiBaru),
            'durasi_lama' => $this->rataDurasi($sesiLama),

            'rentetan_hari' => $this->rentetanHari($sesi),
            'hari_aktif_pekan_ini' => $sesiBaru->map(fn ($s) => $s->completed_at->toDateString())->unique()->count(),
            'ragu' => $this->porsiRagu($sesi),
            'sedang_dipelajari' => QuestionProgress::where('user_id', $pelajar->id)->whereNull('mastered_at')->count(),
        ];
    }

    /**
     * Jumlah soal yang dikuasai sampai tiap tanggal, per paket. Inilah kurva
     * yang mendatar saat anaknya jalan di tempat.
     *
     * @param  Collection<int, Tryout>  $paketSoal
     * @param  Collection<int, array{tryout_id: int, tanggal: string}>  $penguasaan
     * @return array<int, array{tryout: Tryout, titik: list<array{tanggal: string, nilai: int}>}>
     */
    private function kurvaPenguasaan(Collection $paketSoal, Collection $penguasaan): array
    {
        $tanggal = $penguasaan->pluck('tanggal')->unique()->sort()->values();

        return $paketSoal->map(function (Tryout $paket) use ($penguasaan, $tanggal) {
            $milikPaket = $penguasaan->where('tryout_id', $paket->id);
            $berjalan = 0;
            $titik = [];

            foreach ($tanggal as $hari) {
                $berjalan += $milikPaket->where('tanggal', $hari)->count();
                $titik[] = ['tanggal' => $hari, 'nilai' => $berjalan];
            }

            return ['tryout' => $paket, 'titik' => $titik];
        })->all();
    }

    /**
     * Persentase benar tiap sesi, berurut waktu.
     *
     * @param  Collection<int, Tryout>  $paketSoal
     * @param  Collection<int, ExamAttempt>  $sesi
     * @return array<int, array{tryout: Tryout, titik: list<array{tanggal: string, nilai: float}>}>
     */
    private function kurvaAkurasi(Collection $paketSoal, Collection $sesi): array
    {
        return $paketSoal->map(fn (Tryout $paket) => [
            'tryout' => $paket,
            'titik' => $sesi->where('tryout_id', $paket->id)
                ->map(fn (ExamAttempt $s) => [
                    'tanggal' => ($s->session_date ?? $s->completed_at)->toDateString(),
                    'nilai' => round((float) $s->percentage, 1),
                ])
                ->values()
                ->all(),
        ])->all();
    }

    /**
     * @param  Collection<int, Tryout>  $paketSoal
     * @param  Collection<int, ExamAttempt>  $sesi
     * @return list<array<string, mixed>>
     */
    private function rincianPaket(Collection $paketSoal, Collection $sesi, User $pelajar): array
    {
        return $paketSoal->map(function (Tryout $paket) use ($sesi, $pelajar) {
            $milikPaket = $sesi->where('tryout_id', $paket->id);

            return [
                'tryout' => $paket,
                'penguasaan' => $paket->masteryFor($pelajar),
                'sesi' => $milikPaket->count(),
                'akurasi' => $this->rataAkurasi($milikPaket),
                'durasi' => $this->rataDurasi($milikPaket),
            ];
        })->all();
    }

    /**
     * Soal yang berulang kali dijawab salah dan belum juga dikuasai. Di sinilah
     * biasanya letak macetnya.
     *
     * @return Collection<int, QuestionProgress>
     */
    private function soalMenyangkut(User $pelajar): Collection
    {
        return QuestionProgress::with('question.tryout')
            ->where('user_id', $pelajar->id)
            ->whereNull('mastered_at')
            ->where('times_wrong', '>=', self::AMBANG_MENYANGKUT)
            ->orderByDesc('times_wrong')
            ->orderBy('correct_streak')
            ->limit(15)
            ->get();
    }

    /**
     * @param  Collection<int, ExamAttempt>  $sesi
     */
    private function rataAkurasi(Collection $sesi): ?float
    {
        return $sesi->isEmpty() ? null : round((float) $sesi->avg('percentage'), 1);
    }

    /**
     * Rata-rata lama pengerjaan dalam detik, dihitung dari sesi yang punya
     * kedua penanda waktunya.
     *
     * @param  Collection<int, ExamAttempt>  $sesi
     */
    private function rataDurasi(Collection $sesi): ?int
    {
        $durasi = $sesi
            ->filter(fn (ExamAttempt $s) => $s->started_at && $s->completed_at)
            ->map(fn (ExamAttempt $s) => $s->started_at->diffInSeconds($s->completed_at))
            ->filter(fn (int $detik) => $detik > 0);

        return $durasi->isEmpty() ? null : (int) round($durasi->avg());
    }

    /**
     * Berapa hari berturut-turut, dihitung mundur dari hari ini, ada sesi yang
     * selesai. Hari ini boleh belum dikerjakan tanpa memutus rentetan.
     *
     * @param  Collection<int, ExamAttempt>  $sesi
     */
    private function rentetanHari(Collection $sesi): int
    {
        $hari = $sesi->map(fn (ExamAttempt $s) => $s->completed_at?->toDateString())
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if ($hari->isEmpty()) {
            return 0;
        }

        $penunjuk = Carbon::today();

        if ($hari->first() !== $penunjuk->toDateString()) {
            $penunjuk = $penunjuk->subDay();

            if ($hari->first() !== $penunjuk->toDateString()) {
                return 0;
            }
        }

        $rentetan = 0;

        foreach ($hari as $tanggal) {
            if ($tanggal !== $penunjuk->toDateString()) {
                break;
            }

            $rentetan++;
            $penunjuk = $penunjuk->subDay();
        }

        return $rentetan;
    }

    /**
     * Porsi jawaban yang ditandai ragu-ragu. Angka yang menurun menandakan
     * kepercayaan dirinya tumbuh.
     *
     * @param  Collection<int, ExamAttempt>  $sesi
     */
    private function porsiRagu(Collection $sesi): ?float
    {
        $idSesi = $sesi->pluck('id');

        if ($idSesi->isEmpty()) {
            return null;
        }

        $total = ExamAnswer::whereIn('exam_attempt_id', $idSesi)->count();

        if ($total === 0) {
            return null;
        }

        $ragu = ExamAnswer::whereIn('exam_attempt_id', $idSesi)->where('is_doubt', true)->count();

        return round(($ragu / $total) * 100, 1);
    }
}
