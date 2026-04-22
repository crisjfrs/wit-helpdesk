@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user-edit"></i> Edit User</h3>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
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

            @if(auth()->user()->isAdmin() && $user->id !== auth()->id())
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="teknisi" {{ old('role', $user->role) == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" placeholder="Contoh: -1001234567890">
            </div>

            <div class="form-group">
                <label class="form-label d-flex align-items-center gap-2">
                    <input type="checkbox" name="telegram_notifications_enabled" value="1" {{ old('telegram_notifications_enabled', $user->telegram_notifications_enabled) ? 'checked' : '' }}>
                    Aktifkan notifikasi Telegram
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
