@extends('layouts.app')

@section('title', 'Detail Tiket')
@section('page-title', 'Detail Tiket')

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-ticket-alt"></i> {{ $ticket->ticket_number }}</h3>
        <div>
            @can('update', $ticket)
            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-sm btn-warning btn-action-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2 mb-3" style="flex-wrap: wrap;">
            <div><strong>Judul:</strong> {{ $ticket->title }}</div>
            <div><strong>Status:</strong> <span class="badge status-{{ str_replace('_', '-', $ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></div>
            <div><strong>Prioritas:</strong> <span class="badge priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></div>
            <div><strong>Kategori:</strong> {{ $ticket->categoryModel ? $ticket->categoryModel->name : ucfirst($ticket->category) }}</div>
        </div>

        <div class="mb-3">
            <strong>Dibuat oleh:</strong> {{ $ticket->user->name }}<br>
            <strong>Ditugaskan ke:</strong> {{ $ticket->assignedTechnician ? $ticket->assignedTechnician->name : 'Belum ditugaskan' }}<br>
            <strong>Tanggal dibuat:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}
        </div>

        <div class="mb-3">
            <strong>Deskripsi:</strong>
            <div style="background: var(--light); padding: 15px; border-radius: var(--border-radius); margin-top: 10px;">
                {{ $ticket->description }}
            </div>
        </div>

        @if($ticket->attachment)
        <div class="mb-3">
            <strong>Lampiran:</strong>
            <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
        @endif

        @can('changeStatus', $ticket)
        <div class="mb-3">
            <form action="{{ route('tickets.updateStatus', $ticket->id) }}" method="POST" style="display: inline;">
                @csrf
                <select name="status" class="form-select" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="solved" {{ $ticket->status == 'solved' ? 'selected' : '' }}>Solved</option>
                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </form>
        </div>
        @endcan
    </div>
</div>

<!-- Messages/Chat -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-comments"></i> Percakapan</h3>
    </div>
    <div class="card-body">
        <div class="chat-container" id="chat-container">
            @foreach($ticket->messages->sortBy('created_at') as $message)
            <div class="message {{ $message->user_id == auth()->id() ? 'message-sent' : '' }}">
                <div class="message-header">
                    <span><strong>{{ $message->user->name }}</strong></span>
                    <span>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="message-body">
                    {{ $message->message }}
                    @if($message->attachment)
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $message->attachment) }}" target="_blank" class="btn btn-sm btn-outline">
                            <i class="fas fa-paperclip"></i> Lampiran
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <div class="form-group">
                <textarea name="message" class="form-control" rows="3" placeholder="Tulis pesan..." required></textarea>
            </div>
            <div class="form-group">
                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Kirim Pesan
            </button>
        </form>
    </div>
</div>

<!-- Activity Timeline -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Timeline Aktivitas</h3>
    </div>
    <div class="card-body">
        @foreach($ticket->activities->sortByDesc('created_at') as $activity)
        <div class="mb-3" style="padding-left: 20px; border-left: 3px solid var(--primary);">
            <div><strong>{{ $activity->user->name }}</strong> - {{ $activity->description }}</div>
            <small style="color: var(--gray);">{{ $activity->created_at->format('d/m/Y H:i') }}</small>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    // Auto scroll to bottom of chat
    const chatContainer = document.getElementById('chat-container');
    chatContainer.scrollTop = chatContainer.scrollHeight;
</script>
@endpush
@endsection
