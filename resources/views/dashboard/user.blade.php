@extends('layouts.app')

@section('title', 'Dashboard User')
@section('page-title', 'Dashboard User')

@section('content')
<div class="dashboard-shell">
    <div class="dashboard-hero">
        <h2>Portal Tiket Saya</h2>
        <p>
            Buat laporan cepat, pantau progres penanganan, dan lihat update tiket dalam satu tampilan yang lebih rapi.
        </p>
        <div class="hero-actions">
            <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-success btn-action-create"><i class="fas fa-plus"></i> Buat Tiket Baru</a>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline">Lihat Semua Tiket</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value">{{ $stats['my_tickets'] }}</div>
                    <div class="stat-card-label">Total Tiket Saya</div>
                </div>
                <div class="stat-card-icon primary">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value">{{ $stats['open_tickets'] }}</div>
                    <div class="stat-card-label">Tiket Terbuka</div>
                </div>
                <div class="stat-card-icon info">
                    <i class="fas fa-folder-open"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value">{{ $stats['in_progress_tickets'] }}</div>
                    <div class="stat-card-label">Dalam Proses</div>
                </div>
                <div class="stat-card-icon warning">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value">{{ $stats['solved_tickets'] }}</div>
                    <div class="stat-card-label">Tiket Selesai</div>
                </div>
                <div class="stat-card-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Tiket Saya</h3>
            <div class="d-flex gap-1 flex-wrap">
                <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-success btn-action-create">
                    <i class="fas fa-plus"></i> Buat Tiket Baru
                </a>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-primary btn-action-detail">Lihat Semua</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-shell">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Ditugaskan Ke</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myTickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_number }}</td>
                            <td>{{ Str::limit($ticket->title, 40) }}</td>
                            <td><span class="badge status-{{ str_replace('_', '-', $ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                            <td><span class="badge priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td>{{ $ticket->assignedTechnician ? $ticket->assignedTechnician->name : '-' }}</td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary btn-action-detail">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada tiket. <a href="{{ route('tickets.create') }}">Buat tiket baru</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
