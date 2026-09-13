@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-5 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-start sm:justify-between sm:gap-6']) }}>
    <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">{{ $title }}</h2>

        @if($description)
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
