@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user"></i> Detail User</h3>
        <div>
            @can('update', $user)
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning btn-action-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>Nama:</strong> {{ $user->name }}
        </div>
        <div class="mb-3">
            <strong>Email:</strong> {{ $user->email }}
        </div>
        <div class="mb-3">
            <strong>Role:</strong> <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
        </div>
        <div class="mb-3">
            <strong>Status:</strong> 
            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="mb-3">
            <strong>Tanggal Dibuat:</strong> {{ $user->created_at->format('d/m/Y H:i') }}
        </div>
        <div class="mb-3">
            <strong>Terakhir Diupdate:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
        </div>

        @if($user->isUser())
        <div class="card mt-3">
            <div class="card-header">
                <h4>Statistik Tiket</h4>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                    <div>
                        <strong>Total Tiket:</strong> {{ $user->tickets->count() }}
                    </div>
                    <div>
                        <strong>Open:</strong> {{ $user->tickets->where('status', 'open')->count() }}
                    </div>
                    <div>
                        <strong>In Progress:</strong> {{ $user->tickets->where('status', 'in_progress')->count() }}
                    </div>
                    <div>
                        <strong>Solved:</strong> {{ $user->tickets->where('status', 'solved')->count() }}
                    </div>
                    <div>
                        <strong>Closed:</strong> {{ $user->tickets->where('status', 'closed')->count() }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($user->isTeknisi())
        <div class="card mt-3">
            <div class="card-header">
                <h4>Statistik Tiket Ditugaskan</h4>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                    <div>
                        <strong>Total Ditugaskan:</strong> {{ $user->assignedTickets->count() }}
                    </div>
                    <div>
                        <strong>Open:</strong> {{ $user->assignedTickets->where('status', 'open')->count() }}
                    </div>
                    <div>
                        <strong>In Progress:</strong> {{ $user->assignedTickets->where('status', 'in_progress')->count() }}
                    </div>
                    <div>
                        <strong>Solved:</strong> {{ $user->assignedTickets->where('status', 'solved')->count() }}
                    </div>
                    <div>
                        <strong>Closed:</strong> {{ $user->assignedTickets->where('status', 'closed')->count() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
