@extends('layouts.app')

@section('title', 'Verifikasi Email - Helpdesk System')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-brand">
                <div class="auth-brand-mark"><i class="fas fa-envelope-open-text"></i></div>
                <div>
                    <div class="auth-card-title">Verifikasi Email</div>
                    <div class="auth-card-subtitle">Masukkan kode 6 digit untuk menyelesaikan pendaftaran akun</div>
                </div>
            </div>
        </div>

        <div class="auth-card-body">
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <p style="margin-bottom: 20px; color: #cbd5e1; font-size: 14px;">
                Kode dikirim ke: <strong style="color: #ffffff;">{{ $email }}</strong>
            </p>

            <p style="margin-bottom: 20px; color: #cbd5e1; font-size: 14px;">
                Akun akan dibuat setelah kode verifikasi Anda benar.
            </p>

            <form action="{{ route('verification.verify') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Kode Verifikasi</label>
                    <input
                        type="text"
                        name="code"
                        class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                        value="{{ old('code') }}"
                        required
                        inputmode="numeric"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder="Contoh: 123456"
                        autofocus
                    >
                    @error('code')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100" style="margin-bottom: 12px;">
                    <i class="fas fa-shield-check"></i> Verifikasi Email
                </button>
            </form>

            <form action="{{ route('verification.resend') }}" method="POST" style="margin-top: 8px;">
                @csrf
                <button type="submit" class="btn btn-outline w-100">
                    <i class="fas fa-paper-plane"></i> Kirim Ulang Kode
                </button>
            </form>

            <p style="margin-top: 16px; text-align: center; color: #cbd5e1; font-size: 14px;">
                Salah email?
                <a href="{{ route('register') }}" style="color: #60a5fa; text-decoration: none; font-weight: 600;">
                    Daftar ulang
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
