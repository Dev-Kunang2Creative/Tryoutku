<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $attempt->tryout->title }} &middot; Ruang Ujian</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    // A session only serves the questions that were due today.
    $sessionAnswers = $attempt->answers;
    $totalQuestions = $sessionAnswers->count();
@endphp
<body class="flex min-h-full flex-col pb-20 lg:pb-0">
    {{-- Exam header --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-14 max-w-7xl items-center gap-2 px-3 sm:h-16 sm:gap-4 sm:px-6">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium leading-tight text-slate-900">{{ $attempt->tryout->title }}</p>
                <p class="mt-0.5 hidden truncate text-xs leading-tight text-slate-500 sm:block">
                    Sesi hari ini &middot; {{ $totalQuestions }} soal
                </p>
            </div>

            {{-- Autosave state --}}
            <span id="autosave" class="flex shrink-0 items-center gap-1.5 rounded-md px-1.5 py-1 text-xs font-medium text-emerald-700 sm:bg-emerald-50 sm:px-2">
                <span id="autosaveDot" class="size-2 shrink-0 rounded-full bg-emerald-500"></span>
                <span id="autosaveText" class="hidden sm:inline">Tersimpan</span>
            </span>

            {{-- Countdown --}}
            <div id="timer" class="flex shrink-0 items-center gap-1.5 rounded-lg bg-slate-900 px-2.5 py-1.5 text-white"
                role="timer" aria-live="off" title="Waktu tersisa">
                <x-icon name="clock" class="size-4 shrink-0 opacity-70" />
                <span id="countdown" class="text-sm font-semibold tabular sm:text-base">--:--</span>
            </div>

            <form action="{{ route('student.exam.abandon', $attempt) }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm px-2 sm:px-3"
                    title="Keluar tanpa mengumpulkan">
                    <x-icon name="arrow-left" class="size-4" />
                    <span class="sr-only sm:not-sr-only">Keluar</span>
                </button>
            </form>

            <button type="button" data-action="finish" class="btn btn-success btn-sm shrink-0 sm:px-4 sm:py-2.5 sm:text-sm">
                <x-icon name="check-double" class="size-4" />
                Selesai
            </button>
        </div>
    </header>

    <main class="mx-auto grid w-full max-w-7xl flex-1 gap-5 px-3 py-4 sm:px-6 sm:py-6 lg:grid-cols-[minmax(0,1fr)_19rem] lg:gap-6">
        {{-- Question stage --}}
        <div class="card flex min-w-0 flex-col p-4 sm:p-6">
            @foreach($sessionAnswers as $index => $answer)
                @php
                    $question = $answer->question;
                    $chosenOptionId = $answer->question_option_id;
                @endphp

                <section id="pane-{{ $index }}" class="question-pane {{ $index === 0 ? '' : 'hidden' }}"
                    data-index="{{ $index }}"
                    data-question-id="{{ $question->id }}"
                    data-answered="{{ $chosenOptionId ? '1' : '0' }}"
                    data-doubt="{{ $answer->is_doubt ? '1' : '0' }}"
                    aria-label="Soal nomor {{ $index + 1 }}">

                    <header class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-b border-slate-100 pb-3.5">
                        <p class="text-sm font-medium text-slate-900">
                            Soal <span class="tabular">{{ $index + 1 }}</span>
                            <span class="font-normal text-slate-400">dari <span class="tabular">{{ $totalQuestions }}</span></span>
                        </p>

                        <p class="text-xs text-slate-500">
                            Bobot <span class="font-medium text-slate-700 tabular">{{ $question->score_weight }}</span> poin
                        </p>
                    </header>

                    <div class="mt-4 select-text whitespace-pre-line text-[0.9375rem] leading-relaxed text-slate-800 sm:text-base sm:leading-relaxed">{{ $question->question_text }}</div>
                    @if($question->imageUrl())
                        <figure class="mt-4">
                            <img src="{{ $question->imageUrl() }}" alt="Gambar pendukung soal"
                                class="max-h-80 w-auto max-w-full rounded-lg border border-slate-200 bg-white">
                        </figure>
                    @endif

                    <div class="mt-5 space-y-2.5" role="group" aria-label="Pilihan jawaban">
                        @foreach($question->options as $option)
                            @php $isChosen = $chosenOptionId == $option->id; @endphp

                            <button type="button"
                                class="option group flex w-full items-center gap-3 rounded-lg border px-3 py-3 text-left transition-colors {{ $isChosen ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}"
                                aria-pressed="{{ $isChosen ? 'true' : 'false' }}"
                                data-option-id="{{ $option->id }}">
                                <span class="option-key flex size-8 shrink-0 items-center justify-center rounded-md text-sm font-semibold transition-colors {{ $isChosen ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200' }}">
                                    {{ $option->option_key }}
                                </span>

                                <span class="min-w-0 select-text text-sm leading-relaxed text-slate-700">{{ $option->option_text }}</span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endforeach

            {{-- Desktop navigation --}}
            <div class="mt-6 hidden items-center justify-between gap-3 border-t border-slate-100 pt-5 lg:flex">
                <button type="button" data-action="prev" class="btn btn-secondary">
                    <x-icon name="chevron-left" class="size-4" />
                    Sebelumnya
                </button>

                <button type="button" data-action="doubt" class="btn btn-secondary" aria-pressed="false">
                    <x-icon name="flag" class="size-4" />
                    <span data-doubt-label>Tandai ragu-ragu</span>
                </button>

                <button type="button" data-action="next" class="btn btn-primary">
                    <span data-next-label>Selanjutnya</span>
                    <x-icon name="chevron-right" class="size-4" />
                </button>
            </div>

            <p class="mt-4 hidden text-center text-xs text-slate-400 lg:block">
                Pintasan: <kbd class="font-medium text-slate-500">A</kbd>&ndash;<kbd class="font-medium text-slate-500">E</kbd> pilih jawaban
                &middot; <kbd class="font-medium text-slate-500">&larr;</kbd> <kbd class="font-medium text-slate-500">&rarr;</kbd> pindah soal
                &middot; <kbd class="font-medium text-slate-500">R</kbd> tandai ragu
            </p>
        </div>

        {{-- Question palette: sticky sidebar on desktop, bottom sheet on mobile --}}
        <aside id="palette"
            class="invisible fixed inset-x-0 bottom-0 z-50 max-h-[80vh] translate-y-full overflow-y-auto rounded-t-2xl border-t border-slate-200 bg-white p-4 shadow-[0_-8px_30px_-12px_rgb(15_23_42/0.25)] transition-transform duration-200 ease-out
                   lg:visible lg:sticky lg:inset-x-auto lg:bottom-auto lg:top-[5.5rem] lg:z-auto lg:max-h-[calc(100vh-7rem)] lg:translate-y-0 lg:self-start lg:rounded-xl lg:border lg:p-5 lg:shadow-none"
            aria-label="Daftar nomor soal">

            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Nomor soal</h2>

                <button type="button" data-action="close-palette" class="btn btn-ghost btn-sm px-1.5 lg:hidden">
                    <x-icon name="x" class="size-4" />
                    <span class="sr-only">Tutup daftar soal</span>
                </button>
            </div>

            <div class="grid grid-cols-6 gap-2 sm:grid-cols-8 lg:grid-cols-5">
                @foreach($sessionAnswers as $index => $answer)
                    @php
                        $hasAnswer = (bool) $answer->question_option_id;
                    @endphp

                    <button type="button" id="dot-{{ $index }}" data-jump="{{ $index }}"
                        class="palette-dot relative flex aspect-square items-center justify-center rounded-md border text-xs font-medium tabular transition-colors {{ $hasAnswer ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}"
                        aria-label="Soal {{ $index + 1 }}">
                        {{ $index + 1 }}
                        <span class="flag-mark absolute -right-0.5 -top-0.5 size-2.5 rounded-full bg-amber-500 ring-2 ring-white {{ $answer->is_doubt ? '' : 'hidden' }}"></span>
                    </button>
                @endforeach
            </div>

            <dl class="mt-4 space-y-2 border-t border-slate-100 pt-3.5 text-xs">
                <div class="flex items-center gap-2">
                    <span class="size-3 shrink-0 rounded bg-emerald-600"></span>
                    <dt class="text-slate-600">Terjawab</dt>
                    <dd class="ml-auto font-medium text-slate-900 tabular" data-count="answered">0</dd>
                </div>
                <div class="flex items-center gap-2">
                    <span class="size-3 shrink-0 rounded border border-slate-200 bg-white"></span>
                    <dt class="text-slate-600">Belum dijawab</dt>
                    <dd class="ml-auto font-medium text-slate-900 tabular" data-count="unanswered">0</dd>
                </div>
                <div class="flex items-center gap-2">
                    <span class="size-3 shrink-0 rounded-full bg-amber-500"></span>
                    <dt class="text-slate-600">Ditandai ragu</dt>
                    <dd class="ml-auto font-medium text-slate-900 tabular" data-count="doubt">0</dd>
                </div>
            </dl>

            <button type="button" data-action="finish" class="btn btn-success mt-4 w-full lg:hidden">
                <x-icon name="check-double" class="size-4" />
                Selesaikan ujian
            </button>
        </aside>
    </main>

    {{-- Mobile action bar --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden">
        <div class="flex items-center gap-2 px-3 py-2.5">
            <button type="button" data-action="prev" class="btn btn-secondary px-3" aria-label="Soal sebelumnya">
                <x-icon name="chevron-left" class="size-5" />
            </button>

            <button type="button" data-action="doubt" class="btn btn-secondary px-3" aria-pressed="false" aria-label="Tandai ragu-ragu">
                <x-icon name="flag" class="size-5" />
            </button>

            <button type="button" data-action="open-palette" class="btn btn-ghost min-w-0 flex-1 border border-slate-200">
                <x-icon name="grid" class="size-4 shrink-0" />
                <span class="truncate">
                    Soal <span class="tabular" data-current-label>1</span>/<span class="tabular">{{ $totalQuestions }}</span>
                </span>
                <x-icon name="chevron-up" class="size-4 shrink-0 text-slate-400" />
            </button>

            <button type="button" data-action="next" class="btn btn-primary px-3" aria-label="Soal selanjutnya">
                <x-icon name="chevron-right" class="size-5" />
            </button>
        </div>
    </nav>

    {{-- Backdrop for the palette sheet --}}
    <div id="paletteBackdrop" class="fixed inset-0 z-40 hidden bg-slate-900/40 lg:hidden" aria-hidden="true"></div>

    {{-- Finish confirmation --}}
    <div id="finishModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="finishTitle">
        <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl sm:p-6">
            <h2 id="finishTitle" class="text-base font-semibold text-slate-900">Kumpulkan jawaban?</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                Setelah dikumpulkan, jawaban tidak dapat diubah lagi.
            </p>

            <dl class="mt-4 grid grid-cols-3 divide-x divide-slate-200 overflow-hidden rounded-lg border border-slate-200 text-center">
                <div class="px-2 py-3">
                    <dd class="text-lg font-semibold text-emerald-700 tabular" data-count="answered">0</dd>
                    <dt class="mt-0.5 text-xs text-slate-500">Terjawab</dt>
                </div>
                <div class="px-2 py-3">
                    <dd class="text-lg font-semibold text-amber-600 tabular" data-count="doubt">0</dd>
                    <dt class="mt-0.5 text-xs text-slate-500">Ragu</dt>
                </div>
                <div class="px-2 py-3">
                    <dd class="text-lg font-semibold text-slate-500 tabular" data-count="unanswered">0</dd>
                    <dt class="mt-0.5 text-xs text-slate-500">Kosong</dt>
                </div>
            </dl>

            <form id="finishForm" action="{{ route('student.exam.finish', $attempt) }}" method="POST" class="mt-5 flex flex-col-reverse gap-2 sm:flex-row">
                @csrf
                <button type="button" data-action="close-finish" class="btn btn-secondary sm:flex-1">Batal</button>
                <button type="submit" class="btn btn-success sm:flex-1">Ya, kumpulkan</button>
            </form>
        </div>
    </div>

    {{-- Time-up notice --}}
    <div id="timeUpModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/60 p-4" role="alertdialog" aria-modal="true">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center shadow-xl">
            <span class="mx-auto mb-4 flex size-11 items-center justify-center rounded-full bg-red-50 text-red-600">
                <x-icon name="clock" class="size-6" />
            </span>
            <h2 class="text-base font-semibold text-slate-900">Waktu pengerjaan habis</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Jawaban Anda sedang dikumpulkan secara otomatis&hellip;</p>
        </div>
    </div>

    <script>
        (() => {
            const TOTAL = {{ $totalQuestions }};
            const ANSWER_URL = @json(route('student.exam.answer', $attempt));
            const CSRF = document.querySelector('meta[name="csrf-token"]').content;

            let current = 0;
            let remaining = {{ $remainingSeconds }};
            let submitting = false;

            const panes = Array.from(document.querySelectorAll('.question-pane'));
            const dots = Array.from(document.querySelectorAll('.palette-dot'));
            const palette = document.getElementById('palette');
            const paletteBackdrop = document.getElementById('paletteBackdrop');
            const finishModal = document.getElementById('finishModal');
            const finishForm = document.getElementById('finishForm');
            const timeUpModal = document.getElementById('timeUpModal');

            const DOT_ANSWERED = 'border-emerald-600 bg-emerald-600 text-white';
            const DOT_EMPTY = 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50';
            const DOT_CURRENT = 'ring-2 ring-brand-500 ring-offset-1';

            const OPTION_ON = 'border-brand-500 bg-brand-50 ring-1 ring-brand-500';
            const OPTION_OFF = 'border-slate-200 hover:border-slate-300 hover:bg-slate-50';
            const KEY_ON = 'bg-brand-600 text-white';
            const KEY_OFF = 'bg-slate-100 text-slate-600 group-hover:bg-slate-200';

            const swap = (el, remove, add) => {
                el.classList.remove(...remove.split(' '));
                el.classList.add(...add.split(' '));
            };

            /* ---------- Navigation ---------- */

            const goTo = (index) => {
                if (index < 0 || index >= TOTAL || index === current) {
                    return;
                }

                panes[current].classList.add('hidden');
                dots[current].classList.remove(...DOT_CURRENT.split(' '));

                current = index;

                panes[current].classList.remove('hidden');
                dots[current].classList.add(...DOT_CURRENT.split(' '));

                document.querySelectorAll('[data-current-label]').forEach((el) => {
                    el.textContent = current + 1;
                });

                syncControls();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const syncControls = () => {
                const isDoubt = panes[current].dataset.doubt === '1';

                document.querySelectorAll('[data-action="prev"]').forEach((el) => {
                    el.disabled = current === 0;
                });

                document.querySelectorAll('[data-action="next"]').forEach((el) => {
                    el.disabled = current === TOTAL - 1;
                });

                document.querySelectorAll('[data-next-label]').forEach((el) => {
                    el.textContent = current === TOTAL - 1 ? 'Soal terakhir' : 'Selanjutnya';
                });

                document.querySelectorAll('[data-action="doubt"]').forEach((el) => {
                    el.setAttribute('aria-pressed', String(isDoubt));
                    el.classList.toggle('btn-secondary', !isDoubt);
                    el.classList.toggle('bg-amber-500', isDoubt);
                    el.classList.toggle('text-white', isDoubt);
                    el.classList.toggle('hover:bg-amber-600', isDoubt);
                });

                document.querySelectorAll('[data-doubt-label]').forEach((el) => {
                    el.textContent = isDoubt ? 'Ditandai ragu-ragu' : 'Tandai ragu-ragu';
                });
            };

            /* ---------- Status & counters ---------- */

            const refreshDot = (index) => {
                const pane = panes[index];
                const dot = dots[index];
                const answered = pane.dataset.answered === '1';

                swap(dot, answered ? DOT_EMPTY : DOT_ANSWERED, answered ? DOT_ANSWERED : DOT_EMPTY);
                dot.querySelector('.flag-mark').classList.toggle('hidden', pane.dataset.doubt !== '1');
            };

            const refreshCounters = () => {
                const answered = panes.filter((pane) => pane.dataset.answered === '1').length;
                const doubt = panes.filter((pane) => pane.dataset.doubt === '1').length;

                const values = { answered, doubt, unanswered: TOTAL - answered };

                Object.entries(values).forEach(([key, value]) => {
                    document.querySelectorAll(`[data-count="${key}"]`).forEach((el) => {
                        el.textContent = value;
                    });
                });
            };

            /* ---------- Autosave ---------- */

            const autosaveText = document.getElementById('autosaveText');
            const autosaveDot = document.getElementById('autosaveDot');
            const autosaveWrap = document.getElementById('autosave');

            const AUTOSAVE_STATES = {
                saving: { text: 'Menyimpan…', dot: 'bg-amber-500', wrap: 'text-amber-700 sm:bg-amber-50' },
                saved: { text: 'Tersimpan', dot: 'bg-emerald-500', wrap: 'text-emerald-700 sm:bg-emerald-50' },
                failed: { text: 'Gagal menyimpan', dot: 'bg-red-500', wrap: 'text-red-700 sm:bg-red-50' },
            };

            const setAutosave = (state, suffix = '') => {
                const config = AUTOSAVE_STATES[state];

                autosaveWrap.className = `flex shrink-0 items-center gap-1.5 rounded-md px-1.5 py-1 text-xs font-medium sm:px-2 ${config.wrap}`;
                autosaveDot.className = `size-2 shrink-0 rounded-full ${config.dot}`;
                autosaveText.textContent = config.text + suffix;
            };

            const persist = (payload) => {
                setAutosave('saving');

                return fetch(ANSWER_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                    .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
                    .then(({ ok, data }) => {
                        if (data.expired && data.redirect_url) {
                            window.location.href = data.redirect_url;
                            return;
                        }

                        if (!ok) {
                            throw new Error('Request failed');
                        }

                        setAutosave('saved', data.saved_at ? ` ${data.saved_at}` : '');
                    })
                    .catch(() => setAutosave('failed'));
            };

            /* ---------- Answering ---------- */

            panes.forEach((pane, index) => {
                pane.querySelectorAll('.option').forEach((option) => {
                    option.addEventListener('click', () => {
                        pane.querySelectorAll('.option').forEach((sibling) => {
                            const isTarget = sibling === option;
                            const key = sibling.querySelector('.option-key');

                            sibling.setAttribute('aria-pressed', String(isTarget));
                            swap(sibling, isTarget ? OPTION_OFF : OPTION_ON, isTarget ? OPTION_ON : OPTION_OFF);
                            swap(key, isTarget ? KEY_OFF : KEY_ON, isTarget ? KEY_ON : KEY_OFF);
                        });

                        pane.dataset.answered = '1';
                        refreshDot(index);
                        refreshCounters();

                        persist({
                            question_id: Number(pane.dataset.questionId),
                            question_option_id: Number(option.dataset.optionId),
                        });
                    });
                });
            });

            const toggleDoubt = () => {
                const pane = panes[current];
                const next = pane.dataset.doubt === '1' ? '0' : '1';

                pane.dataset.doubt = next;
                refreshDot(current);
                refreshCounters();
                syncControls();

                persist({
                    question_id: Number(pane.dataset.questionId),
                    is_doubt: next === '1' ? 1 : 0,
                });
            };

            /* ---------- Palette sheet (mobile) ---------- */

            const setPaletteOpen = (isOpen) => {
                palette.classList.toggle('translate-y-full', !isOpen);
                palette.classList.toggle('invisible', !isOpen);
                paletteBackdrop.classList.toggle('hidden', !isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
            };

            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            /* ---------- Finish modal ---------- */

            const setFinishOpen = (isOpen) => {
                refreshCounters();
                finishModal.classList.toggle('hidden', !isOpen);
                finishModal.classList.toggle('flex', isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
            };

            finishForm.addEventListener('submit', () => {
                submitting = true;
            });

            /* ---------- Global click routing ---------- */

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-action], [data-jump]');

                if (!trigger) {
                    return;
                }

                if (trigger.dataset.jump !== undefined) {
                    goTo(Number(trigger.dataset.jump));

                    if (!isDesktop()) {
                        setPaletteOpen(false);
                    }

                    return;
                }

                switch (trigger.dataset.action) {
                    case 'prev':
                        goTo(current - 1);
                        break;
                    case 'next':
                        goTo(current + 1);
                        break;
                    case 'doubt':
                        toggleDoubt();
                        break;
                    case 'open-palette':
                        setPaletteOpen(true);
                        break;
                    case 'close-palette':
                        setPaletteOpen(false);
                        break;
                    case 'finish':
                        setPaletteOpen(false);
                        setFinishOpen(true);
                        break;
                    case 'close-finish':
                        setFinishOpen(false);
                        break;
                }
            });

            paletteBackdrop.addEventListener('click', () => setPaletteOpen(false));

            finishModal.addEventListener('click', (event) => {
                if (event.target === finishModal) {
                    setFinishOpen(false);
                }
            });

            /* ---------- Countdown ---------- */

            const timer = document.getElementById('timer');
            const countdown = document.getElementById('countdown');

            const TIMER_BASE = 'flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5';
            const TIMER_STATES = [
                { limit: 180, classes: `${TIMER_BASE} bg-red-600 text-white` },
                { limit: 600, classes: `${TIMER_BASE} bg-amber-500 text-white` },
                { limit: Infinity, classes: `${TIMER_BASE} bg-slate-900 text-white` },
            ];

            const tick = () => {
                if (remaining <= 0) {
                    countdown.textContent = '00:00';
                    timeUpModal.classList.replace('hidden', 'flex');

                    if (!submitting) {
                        submitting = true;
                        finishForm.submit();
                    }

                    return;
                }

                const hours = Math.floor(remaining / 3600);
                const minutes = Math.floor((remaining % 3600) / 60);
                const seconds = remaining % 60;
                const pad = (value) => String(value).padStart(2, '0');

                countdown.textContent = hours > 0
                    ? `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
                    : `${pad(minutes)}:${pad(seconds)}`;

                timer.className = TIMER_STATES.find((state) => remaining <= state.limit).classes;

                remaining -= 1;
            };

            const interval = setInterval(() => {
                tick();

                if (remaining <= 0) {
                    clearInterval(interval);
                }
            }, 1000);

            /* ---------- Keyboard shortcuts ---------- */

            document.addEventListener('keydown', (event) => {
                if (event.metaKey || event.ctrlKey || event.altKey) {
                    return;
                }

                if (!finishModal.classList.contains('hidden')) {
                    if (event.key === 'Escape') {
                        setFinishOpen(false);
                    }

                    return;
                }

                if (event.key === 'Escape') {
                    setPaletteOpen(false);
                    return;
                }

                const key = event.key.toUpperCase();
                const optionIndex = ['A', 'B', 'C', 'D', 'E'].indexOf(key);

                if (optionIndex > -1) {
                    panes[current].querySelectorAll('.option')[optionIndex]?.click();
                    return;
                }

                if (event.key === 'ArrowRight') {
                    goTo(current + 1);
                } else if (event.key === 'ArrowLeft') {
                    goTo(current - 1);
                } else if (key === 'R') {
                    toggleDoubt();
                }
            });

            /* ---------- Boot ---------- */

            dots[0].classList.add(...DOT_CURRENT.split(' '));
            syncControls();
            refreshCounters();
            tick();
        })();
    </script>
</body>
</html>
