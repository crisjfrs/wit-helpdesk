@extends('layouts.app')

@section('title', 'Login - Helpdesk System')

@section('content')
<div class="auth-page">
    <div class="auth-layout">
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
                    <div class="auth-card-title">Selamat Datang</div>
                    <div class="auth-card-subtitle">Silakan masuk untuk melanjutkan</div>
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

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div style="color: var(--danger); font-size: 12px; margin-top: 5px;">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" required>
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

                        <div class="auth-helper-row">
                            <label class="auth-checkbox">
                                <input type="checkbox" name="remember">
                                <span>Ingat saya</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="auth-inline-link">Lupa password?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary w-100 auth-submit-btn">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </button>

                        <p class="auth-switch-copy">
                            Belum punya akun?
                            <a href="{{ route('register') }}" class="auth-inline-link">Daftar di sini</a>
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
