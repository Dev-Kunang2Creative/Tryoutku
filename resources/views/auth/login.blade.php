@extends('layouts.guest')

@section('title', 'Masuk')
@section('heading', 'Masuk ke portal latihan')
@section('subheading', 'Masuk untuk memulai latihan harian.')

@section('content')
<form action="{{ route('login.post') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
            autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Masukkan username"
            class="form-input">
        <x-input-error for="username" />
    </div>

    <div>
        <label for="password" class="form-label">Kata sandi</label>
        <input type="password" id="password" name="password" required autocomplete="current-password"
            placeholder="Masukkan kata sandi" class="form-input">
        <x-input-error for="password" />
    </div>

    <label class="flex cursor-pointer items-center gap-2.5 py-1 text-sm text-slate-600">
        <input type="checkbox" name="remember" value="1" class="form-checkbox" {{ old('remember') ? 'checked' : '' }}>
        Ingat saya di perangkat ini
    </label>

    <button type="submit" class="btn btn-primary btn-lg w-full">Masuk</button>
</form>

@endsection
