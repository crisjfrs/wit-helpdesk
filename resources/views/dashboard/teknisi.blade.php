@extends('layouts.app')

@section('title', 'Dashboard Teknisi')
@section('page-title', 'Dashboard Teknisi')

@section('content')
<div class="dashboard-shell">
    <div class="dashboard-hero">
        <h2>Workspace Teknisi</h2>
        <p>
            Fokus pada tiket prioritas, update progres real-time, dan selesaikan penugasan tanpa kehilangan konteks.
        </p>
        <div class="hero-actions">
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline">Semua Penugasan</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value">{{ $stats['assigned_tickets'] }}</div>
                    <div class="stat-card-label">Tiket Ditugaskan</div>
                </div>
                <div class="stat-card-icon primary">
                    <i class="fas fa-tasks"></i>
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
            <h3><i class="fas fa-list"></i> Tiket Ditugaskan ke Saya</h3>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body">
            <div class="table-shell">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Judul</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedTickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_number }}</td>
                            <td>{{ Str::limit($ticket->title, 40) }}</td>
                            <td>{{ $ticket->user->name }}</td>
                            <td><span class="badge status-{{ str_replace('_', '-', $ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                            <td><span class="badge priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada tiket ditugaskan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
