@extends('layouts.app')

@section('title', 'Register - Helpdesk System')

@section('content')
<div class="auth-page">
    <div class="auth-layout auth-layout-register">
        <section class="auth-visual auth-visual-login" style="--auth-hero-image: url('{{ asset('images/login-hero.jpg') }}');">
            <div class="auth-visual-media" aria-hidden="true"></div>
            <div class="auth-visual-overlay" aria-hidden="true"></div>
            <div class="auth-visual-content">
                <div class="auth-brand auth-brand-visual">
                    <div class="auth-brand-mark">
                        <img src="{{ asset('images/logo-wit.png') }}" alt="WIT Helpdesk" class="auth-brand-mark-image">
                    </div>
                    <div>
                        <div class="auth-card-title">WIT Helpdesk</div>
                        <div class="auth-card-subtitle">Operational Desk</div>
                    </div>
                </div>
                <h1 class="auth-visual-title auth-visual-title-blend auth-visual-title-center">Sistem Monitoring Tiket dan Operasional</h1>
                <div class="auth-feature-grid">
                    <div class="auth-feature-card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Monitoring Real-Time</h3>
                        <p>Pantau SLA dan beban tiket secara langsung.</p>
                    </div>
                    <div class="auth-feature-card">
                        <i class="fas fa-users-cog"></i>
                        <h3>Kolaborasi Tim</h3>
                        <p>Distribusi kerja teknisi lebih terstruktur.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-header">
                    <div class="auth-card-title">Buat Akun</div>
                    <div class="auth-card-subtitle">Isi data berikut untuk mulai menggunakan sistem</div>
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

                        <button type="submit" class="btn btn-primary w-100 auth-submit-btn">
                            <i class="fas fa-user-check"></i> Daftar
                        </button>

                        <p class="auth-switch-copy">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="auth-inline-link">Login di sini</a>
                        </p>
                    </form>
                </div>
            </div>
        </section>
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