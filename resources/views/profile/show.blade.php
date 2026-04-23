@extends('layouts.app')

@section('title', 'Profil')
@section('page-title', 'Profil Saya')

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-user"></i> Informasi Profil</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" placeholder="Contoh: -1001234567890">
                <small class="text-muted">Isi chat ID pribadi/grup Telegram untuk menerima notifikasi tiket.</small>
            </div>

            <div class="form-group">
                <label class="form-label d-flex align-items-center gap-2">
                    <input type="checkbox" name="telegram_notifications_enabled" value="1" {{ old('telegram_notifications_enabled', $user->telegram_notifications_enabled) ? 'checked' : '' }}>
                    Aktifkan notifikasi Telegram
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-key"></i> Ganti Password</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Password Saat Ini</label>
                <div class="password-wrapper">
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye" id="current_password-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="new_password" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye" id="new_password-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Ganti Password
            </button>
        </form>
    </div>
</div>

@endsection
