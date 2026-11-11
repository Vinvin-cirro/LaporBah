
@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="auth-card">

    <div class="auth-heading">
        <h2>Selamat datang!</h2>
        <p>Masuk ke akun LaporBah untuk membantu menjadikan Medan lebih aman.</p>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login.process') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="Masukkan email"
                value="{{ old('email') }}"
                required
                autofocus
            >
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password"
                required
            >
        </div>

        <button type="submit" class="btn-primary">
            Masuk
        </button>
    </form>

    <div class="auth-footer">
        Belum memiliki akun?
        <a href="{{ route('register') }}">Daftar sekarang</a>
    </div>

</div>
@endsection
