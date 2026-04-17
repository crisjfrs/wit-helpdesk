@extends('layouts.app')

@section('title', 'Register - Helpdesk System')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-brand">
                <div class="auth-brand-mark"><i class="fas fa-user-plus"></i></div>
                <div>
                    <div class="auth-card-title">Buat Akun Helpdesk</div>
                    <div class="auth-card-subtitle">Daftar dengan email dan password Anda sendiri</div>
                </div>
            </div>
        </div>
        <div class="auth-card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" required placeholder="nama@gmail.com">
                    @error('email')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye" id="password_confirmation-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-user-check"></i> Daftar
                </button>

                <p style="margin-top: 16px; text-align: center; color: #cbd5e1; font-size: 14px;">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" style="color: #60a5fa; text-decoration: none; font-weight: 600;">
                        Login di sini
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-icon');

    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}
</script>
@endsection