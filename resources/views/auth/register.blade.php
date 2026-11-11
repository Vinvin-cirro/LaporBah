
@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
<div class="auth-card">

    <div class="auth-heading">
        <h2>Buat akun</h2>
        <p>Daftar sebagai warga untuk mulai melaporkan kondisi lingkungan sekitar.</p>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('register.process') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nama lengkap</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="Masukkan nama lengkap"
                value="{{ old('name') }}"
                required
            >
            @error('name')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

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
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Minimal 8 karakter"
                required
            >
            @error('password')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-control"
                placeholder="Masukkan ulang password"
                required
            >
        </div>

        <button type="submit" class="btn-primary">
            Daftar sebagai warga
        </button>
    </form>

    <div class="auth-footer">
        Sudah memiliki akun?
        <a href="{{ route('login') }}">Masuk</a>
    </div>

</div>
@endsection
