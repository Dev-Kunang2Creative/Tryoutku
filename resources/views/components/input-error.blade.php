@props(['for'])

@error($for)
    <p class="form-error">
        <x-icon name="alert-triangle" class="mt-px size-3.5 shrink-0" />
        <span>{{ $message }}</span>
    </p>
@enderror
