@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> Manajemen User</h3>
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm btn-action-create">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="mb-3">
            <div class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari user..." value="{{ request('search') }}" style="flex: 1;">
                <select name="role" class="form-select" style="width: 150px;">
                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>Semua Role</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="teknisi" {{ request('role') == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm btn-search-submit btn-action-search">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-primary">{{ ucfirst($user->role) }}</span></td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td style="min-width: 300px;">
                        <div class="action-buttons" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; white-space: nowrap;">
                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-primary btn-action-detail">Detail</a>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning btn-action-edit">Edit</a>
                            @can('toggleActive', $user)
                            <form action="{{ route('users.toggleActive', $user->id) }}" method="POST" style="display: inline-block; margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-action-toggle {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @endcan
                            @can('delete', $user)
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-action-delete">Hapus</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada user</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $users->links() }}
    </div>
</div>
@endsection
