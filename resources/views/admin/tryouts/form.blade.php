@php
    /** @var \App\Models\Tryout|null $tryout */
    $tryout = $tryout ?? null;

    $sessionTypes = [
        'Latihan Harian' => 'Latihan Harian',
        'Pengulangan Intensif' => 'Pengulangan Intensif',
    ];
@endphp

<div class="space-y-5">
    <div>
        <label for="title" class="form-label">Judul sesi latihan <span class="text-red-500">*</span></label>
        <input type="text" id="title" name="title" value="{{ old('title', $tryout?->title) }}" required class="form-input"
            placeholder="Latihan Harian IPA">
        <x-input-error for="title" />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="subject" class="form-label">Mata pelajaran <span class="text-red-500">*</span></label>
            <input type="text" id="subject" name="subject"
                value="{{ old('subject', $tryout?->subject) }}" required class="form-input"
                placeholder="IPA atau Bahasa Inggris">
            <x-input-error for="subject" />
        </div>

        <div>
            <label for="session_type" class="form-label">Tipe sesi <span class="text-red-500">*</span></label>
            <select id="session_type" name="session_type" class="form-select">
                @foreach($sessionTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('session_type', $tryout?->session_type) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error for="session_type" />
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="duration_minutes" class="form-label">Durasi pengerjaan <span class="text-red-500">*</span></label>
            <div class="relative">
                <input type="number" id="duration_minutes" name="duration_minutes"
                    value="{{ old('duration_minutes', $tryout?->duration_minutes ?? 45) }}"
                    min="5" max="300" required class="form-input pr-16">
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">menit</span>
            </div>
            <p class="form-hint">Timer peserta menghitung mundur sesuai durasi ini.</p>
            <x-input-error for="duration_minutes" />
        </div>

        <div>
            <label for="questions_per_session" class="form-label">Soal per sesi harian <span class="text-red-500">*</span></label>
            <div class="relative">
                <input type="number" id="questions_per_session" name="questions_per_session"
                    value="{{ old('questions_per_session', $tryout?->questions_per_session ?? 15) }}"
                    min="1" max="100" required class="form-input pr-12">
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">soal</span>
            </div>
            <p class="form-hint">Jumlah soal yang diambil dari daftar jatuh tempo setiap hari.</p>
            <x-input-error for="questions_per_session" />
        </div>
    </div>

    <div>
        <label for="description" class="form-label">Petunjuk pengerjaan</label>
        <textarea id="description" name="description" rows="3" class="form-input resize-y"
            placeholder="Catatan singkat tentang paket ini&hellip;">{{ old('description', $tryout?->description) }}</textarea>
        <x-input-error for="description" />
    </div>

    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3.5">
        <input type="checkbox" name="is_active" value="1" class="form-checkbox mt-0.5"
            @checked(old('is_active', $tryout?->is_active ?? true))>
        <span>
            <span class="block text-sm font-medium text-slate-900">Aktifkan paket ini</span>
            <span class="mt-0.5 block text-xs leading-relaxed text-slate-500">
                Paket aktif langsung tampil di halaman latihan harian.
            </span>
        </span>
    </label>
</div>
