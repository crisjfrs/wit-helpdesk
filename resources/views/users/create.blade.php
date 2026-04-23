@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user-plus"></i> Tambah User</h3>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="teknisi" {{ old('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id') }}" placeholder="Contoh: -1001234567890">
            </div>

            <div class="form-group">
                <label class="form-label d-flex align-items-center gap-2">
                    <input type="checkbox" name="telegram_notifications_enabled" value="1" {{ old('telegram_notifications_enabled', 1) ? 'checked' : '' }}>
                    Aktifkan notifikasi Telegram
                </label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
