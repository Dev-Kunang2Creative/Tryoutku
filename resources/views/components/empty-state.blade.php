@props(['icon' => 'file-text', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-12 text-center sm:py-16']) }}>
    <span class="mb-4 flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-icon :name="$icon" class="size-5" />
    </span>

    <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>

    @if($description)
        <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-slate-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">
            {{ $action }}
        </div>
    @endisset
</div>
