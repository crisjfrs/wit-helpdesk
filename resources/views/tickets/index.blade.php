@extends('layouts.app')

@section('title', 'Daftar Tiket')
@section('page-title', 'Daftar Tiket')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Daftar Tiket</h3>
        @can('create', App\Models\Ticket::class)
        <a href="{{ route('tickets.create') }}" class="btn btn-success btn-sm btn-action-create">
            <i class="fas fa-plus"></i> Buat Tiket Baru
        </a>
        @endcan
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('tickets.index') }}" class="mb-3">
            <div class="d-flex gap-2" style="flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari tiket..." value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
                <select name="status" class="form-select" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="solved" {{ request('status') == 'solved' ? 'selected' : '' }}>Solved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <select name="category" class="form-select" style="width: 150px;">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm btn-action-search">
                    <i class="fas fa-search"></i> Cari
                </button>
                @if(request()->hasAny(['search', 'status', 'category']))
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>No. Tiket</th>
                    <th>Judul</th>
                    @if(auth()->user()->isAdmin())
                    <th>User</th>
                    @endif
                    <th>Kategori</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    @if(auth()->user()->isAdmin() || auth()->user()->isTeknisi())
                    <th>Ditugaskan Ke</th>
                    @endif
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ Str::limit($ticket->title, 50) }}</td>
                    @if(auth()->user()->isAdmin())
                    <td>{{ $ticket->user->name }}</td>
                    @endif
                    <td>{{ $ticket->categoryModel ? $ticket->categoryModel->name : ucfirst($ticket->category) }}</td>
                    <td><span class="badge priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                    <td><span class="badge status-{{ str_replace('_', '-', $ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                    @if(auth()->user()->isAdmin() || auth()->user()->isTeknisi())
                    <td>{{ $ticket->assignedTechnician ? $ticket->assignedTechnician->name : '-' }}</td>
                    @endif
                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary btn-action-detail">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? '9' : (auth()->user()->isTeknisi() ? '8' : '7') }}" class="text-center">Tidak ada tiket</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-between align-center">
            <div>{{ $tickets->links() }}</div>
            <div>Total: {{ $tickets->total() }} tiket</div>
        </div>
    </div>
</div>
@endsection
