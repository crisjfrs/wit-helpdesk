@extends('layouts.app')

@section('title', 'Detail Kategori')
@section('page-title', 'Detail Kategori')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tag"></i> Detail Kategori</h3>
        <div>
            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-warning btn-action-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>Nama:</strong> {{ $category->name }}
        </div>
        <div class="mb-3">
            <strong>Slug:</strong> <code>{{ $category->slug }}</code>
        </div>
        <div class="mb-3">
            <strong>Deskripsi:</strong> {{ $category->description ?: '-' }}
        </div>
        <div class="mb-3">
            <strong>Status:</strong> 
            <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-danger' }}">
                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="mb-3">
            <strong>Urutan Tampil:</strong> {{ $category->sort_order }}
        </div>
        <div class="mb-3">
            <strong>Jumlah Tiket:</strong> {{ $category->tickets()->count() }}
        </div>
        <div class="mb-3">
            <strong>Tanggal Dibuat:</strong> {{ $category->created_at->format('d/m/Y H:i') }}
        </div>
        <div class="mb-3">
            <strong>Terakhir Diupdate:</strong> {{ $category->updated_at->format('d/m/Y H:i') }}
        </div>

        @if($category->tickets()->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h4>Tiket dengan Kategori Ini</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->tickets()->latest()->take(10)->get() as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_number }}</td>
                            <td>{{ Str::limit($ticket->title, 40) }}</td>
                            <td><span class="badge status-{{ str_replace('_', '-', $ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                            <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary btn-action-detail">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($category->tickets()->count() > 10)
                <div class="text-center mt-2">
                    <a href="{{ route('tickets.index', ['category' => $category->slug]) }}" class="btn btn-sm btn-outline">
                        Lihat Semua ({{ $category->tickets()->count() }} tiket)
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
