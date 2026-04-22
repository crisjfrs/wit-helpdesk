@extends('layouts.app')

@section('title', 'Manajemen Kategori')
@section('page-title', 'Manajemen Kategori')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tags"></i> Manajemen Kategori</h3>
        <a href="{{ route('categories.create') }}" class="btn btn-success btn-sm btn-action-create">
            <i class="fas fa-plus"></i> Tambah Kategori
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('categories.index') }}" class="mb-3">
            <div class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari kategori..." value="{{ request('search') }}" style="flex: 1;">
                <select name="status" class="form-select" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm btn-action-search">
                    <i class="fas fa-search"></i> Cari
                </button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Urutan</th>
                    <th>Jumlah Tiket</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td><code>{{ $category->slug }}</code></td>
                    <td>{{ Str::limit($category->description, 50) ?: '-' }}</td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>{{ $category->sort_order }}</td>
                    <td>{{ $category->tickets()->count() }}</td>
                    <td style="min-width: 300px;">
                        <div class="action-buttons" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; white-space: nowrap;">
                            <a href="{{ route('categories.show', $category->id) }}" class="btn btn-sm btn-primary btn-action-detail">Detail</a>
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-warning btn-action-edit">Edit</a>
                            <form action="{{ route('categories.toggleActive', $category->id) }}" method="POST" style="display: inline-block; margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-action-toggle {{ $category->is_active ? 'btn-danger' : 'btn-success' }}">
                                    {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-action-delete">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada kategori</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $categories->links() }}
    </div>
</div>
@endsection
