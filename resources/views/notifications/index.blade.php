@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-bell"></i> Notifikasi</h3>
        <form action="{{ route('notifications.readAll') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">Tandai Semua Sudah Dibaca</button>
        </form>
    </div>
    <div class="card-body">
        @forelse($notifications as $notification)
        <div class="card mb-2" style="{{ !$notification->is_read ? 'border-left: 4px solid var(--primary);' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-between align-center">
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 5px;">{{ $notification->title }}</h4>
                        <p style="margin-bottom: 5px;">{{ $notification->message }}</p>
                        <small style="color: var(--gray);">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div>
                        @if($notification->ticket_id)
                        <a href="{{ route('tickets.show', $notification->ticket_id) }}" class="btn btn-sm btn-primary btn-action-detail">Lihat Tiket</a>
                        @endif
                        @if(!$notification->is_read)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline">Tandai Dibaca</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center" style="padding: 40px;">
            <i class="fas fa-bell-slash" style="font-size: 48px; color: var(--gray);"></i>
            <p style="margin-top: 20px; color: var(--gray);">Tidak ada notifikasi</p>
        </div>
        @endforelse

        {{ $notifications->links() }}
    </div>
</div>
@endsection
