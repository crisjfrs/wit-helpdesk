<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramTicketNotification;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['user', 'assignedTechnician', 'categoryModel']);

        // Filter based on role (use case-insensitive check for shared hosting compatibility)
        $role = strtolower(trim($user->role ?? ''));
        if ($role === 'admin') {
            // Admin sees all tickets
        } elseif ($role === 'teknisi') {
            // Teknisi sees assigned tickets (use type casting for ID comparison)
            $query->where('assigned_to', (int)$user->id);
        } else {
            // User sees only their tickets (use type casting for ID comparison)
            $query->where('user_id', (int)$user->id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            // Support both category_id and category slug for backward compatibility
            if (is_numeric($request->category)) {
                $query->where('category_id', $request->category);
            } else {
                $query->where('category', $request->category);
            }
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->latest()->paginate(15);
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('tickets.index', compact('tickets', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('create', Ticket::class);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user role directly
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            if ($role !== 'user') {
                abort(403, 'This action is unauthorized.');
            }
        }
        
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('create', Ticket::class);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user role directly
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            if ($role !== 'user') {
                abort(403, 'This action is unauthorized.');
            }
        }

        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['ticket_number'] = Ticket::generateTicketNumber();
        $data['status'] = 'open';
        
        // Hanya gunakan category_id, tidak perlu set category ENUM lagi
        // Kolom category ENUM tetap ada untuk backward compatibility tapi tidak diisi
        if (!isset($data['category_id'])) {
            // Fallback: jika masih menggunakan category (slug) dari form lama
            if (isset($data['category'])) {
                $category = \App\Models\Category::where('slug', $data['category'])->first();
                if ($category) {
                    $data['category_id'] = $category->id;
                }
            }
        }
        
        // Hapus category dari data jika ada (untuk menghindari error ENUM)
        unset($data['category']);

        // Handle attachment
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('tickets', 'public');
        }

        $ticket = Ticket::create($data);

        // Create activity log
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => 'Tiket dibuat',
        ]);

        // Notify admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'ticket_id' => $ticket->id,
                'type' => 'ticket_created',
                'title' => 'Tiket Baru',
                'message' => "Tiket baru dibuat: {$ticket->title}",
            ]);
        }

        SendTelegramTicketNotification::dispatch($ticket);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Tiket berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('view', $ticket);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user access directly
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            $canView = false;
            
            // Admin can view all tickets
            if ($role === 'admin') {
                $canView = true;
            }
            // Teknisi can view assigned tickets or tickets they created
            elseif ($role === 'teknisi') {
                $canView = (int)$ticket->assigned_to === (int)$user->id || (int)$ticket->user_id === (int)$user->id;
            }
            // User can only view their own tickets
            else {
                $canView = (int)$ticket->user_id === (int)$user->id;
            }
            
            if (!$canView) {
                abort(403, 'This action is unauthorized.');
            }
        }

        $ticket->load(['user', 'assignedTechnician', 'categoryModel', 'messages.user', 'activities.user']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('update', $ticket);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user access directly
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            $canUpdate = false;
            
            // Admin can update all tickets
            if ($role === 'admin') {
                $canUpdate = true;
            }
            // Teknisi can update assigned tickets
            elseif ($role === 'teknisi') {
                $canUpdate = (int)$ticket->assigned_to === (int)$user->id;
            }
            // User can update their own tickets if status is open
            else {
                $canUpdate = (int)$ticket->user_id === (int)$user->id && $ticket->status === 'open';
            }
            
            if (!$canUpdate) {
                abort(403, 'This action is unauthorized.');
            }
        }
        
        $teknisi = User::where('role', 'teknisi')->where('is_active', true)->get();
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return view('tickets.edit', compact('ticket', 'teknisi', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('update', $ticket);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user access directly
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            $canUpdate = false;
            
            // Admin can update all tickets
            if ($role === 'admin') {
                $canUpdate = true;
            }
            // Teknisi can update assigned tickets
            elseif ($role === 'teknisi') {
                $canUpdate = (int)$ticket->assigned_to === (int)$user->id;
            }
            // User can update their own tickets if status is open
            else {
                $canUpdate = (int)$ticket->user_id === (int)$user->id && $ticket->status === 'open';
            }
            
            if (!$canUpdate) {
                abort(403, 'This action is unauthorized.');
            }
        }

        $data = $request->validated();
        $oldStatus = $ticket->status;
        $oldAssignedTo = $ticket->assigned_to;
        
        // Hanya gunakan category_id, tidak perlu set category ENUM lagi
        if (!isset($data['category_id']) && isset($data['category'])) {
            // Fallback: jika masih menggunakan category (slug) dari form lama
            $category = \App\Models\Category::where('slug', $data['category'])->first();
            if ($category) {
                $data['category_id'] = $category->id;
            }
        }
        
        // Hapus category dari data jika ada (untuk menghindari error ENUM)
        unset($data['category']);

        // Handle attachment
        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($ticket->attachment) {
                Storage::disk('public')->delete($ticket->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('tickets', 'public');
        }

        // Update status timestamps
        if (isset($data['status'])) {
            if ($data['status'] === 'solved' && $ticket->status !== 'solved') {
                $data['solved_at'] = now();
            }
            if ($data['status'] === 'closed' && $ticket->status !== 'closed') {
                $data['closed_at'] = now();
            }
        }

        $ticket->update($data);

        // Create activity log
        $activities = [];

        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $activities[] = [
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'description' => "Status diubah dari {$oldStatus} menjadi {$data['status']}",
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $data['status']],
            ];
        }

        if (isset($data['assigned_to']) && $data['assigned_to'] != $oldAssignedTo) {
            $activities[] = [
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'assigned',
                'description' => $data['assigned_to'] 
                    ? "Tiket ditugaskan ke " . User::find($data['assigned_to'])->name
                    : "Penugasan tiket dibatalkan",
                'old_values' => ['assigned_to' => $oldAssignedTo],
                'new_values' => ['assigned_to' => $data['assigned_to']],
            ];

            // Notify assigned teknisi
            if ($data['assigned_to']) {
                Notification::create([
                    'user_id' => $data['assigned_to'],
                    'ticket_id' => $ticket->id,
                    'type' => 'ticket_assigned',
                    'title' => 'Tiket Ditugaskan',
                    'message' => "Anda ditugaskan untuk menangani tiket: {$ticket->title}",
                ]);
            }
        }

        foreach ($activities as $activity) {
            TicketActivity::create($activity);
        }

        // Notify user if status changed
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            Notification::create([
                'user_id' => $ticket->user_id,
                'ticket_id' => $ticket->id,
                'type' => 'ticket_updated',
                'title' => 'Status Tiket Diubah',
                'message' => "Status tiket '{$ticket->title}' diubah menjadi {$data['status']}",
            ]);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Tiket berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('delete', $ticket);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user is admin
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            if ($role !== 'admin') {
                abort(403, 'This action is unauthorized.');
            }
        }

        // Delete attachment
        if ($ticket->attachment) {
            Storage::disk('public')->delete($ticket->attachment);
        }

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket berhasil dihapus.');
    }

    /**
     * Assign ticket to teknisi
     */
    public function assign(Request $request, Ticket $ticket)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('assign', $ticket);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user is admin
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            if ($role !== 'admin') {
                abort(403, 'This action is unauthorized.');
            }
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldAssignedTo = $ticket->assigned_to;
        $ticket->update(['assigned_to' => $request->assigned_to]);

        // Create activity
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'description' => "Tiket ditugaskan ke " . User::find($request->assigned_to)->name,
            'old_values' => ['assigned_to' => $oldAssignedTo],
            'new_values' => ['assigned_to' => $request->assigned_to],
        ]);

        // Notify teknisi
        Notification::create([
            'user_id' => $request->assigned_to,
            'ticket_id' => $ticket->id,
            'type' => 'ticket_assigned',
            'title' => 'Tiket Ditugaskan',
            'message' => "Anda ditugaskan untuk menangani tiket: {$ticket->title}",
        ]);

        return back()->with('success', 'Tiket berhasil ditugaskan.');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        // Check authorization with fallback for shared hosting compatibility
        try {
            $this->authorize('changeStatus', $ticket);
        } catch (AuthorizationException $e) {
            // Fallback check: verify user access directly
            $user = Auth::user();
            if (!$user) {
                abort(403, 'This action is unauthorized.');
            }
            
            $role = strtolower(trim($user->role ?? ''));
            $canChangeStatus = false;
            
            // Admin can change status of all tickets
            if ($role === 'admin') {
                $canChangeStatus = true;
            }
            // Teknisi can change status of assigned tickets
            elseif ($role === 'teknisi') {
                $canChangeStatus = (int)$ticket->assigned_to === (int)$user->id;
            }
            
            if (!$canChangeStatus) {
                abort(403, 'This action is unauthorized.');
            }
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,solved,closed',
        ]);

        $oldStatus = $ticket->status;
        $data = ['status' => $request->status];

        if ($request->status === 'solved' && $ticket->status !== 'solved') {
            $data['solved_at'] = now();
        }
        if ($request->status === 'closed' && $ticket->status !== 'closed') {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        // Create activity
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'description' => "Status diubah dari {$oldStatus} menjadi {$request->status}",
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status],
        ]);

        // Notify user
        Notification::create([
            'user_id' => $ticket->user_id,
            'ticket_id' => $ticket->id,
            'type' => 'ticket_updated',
            'title' => 'Status Tiket Diubah',
            'message' => "Status tiket '{$ticket->title}' diubah menjadi {$request->status}",
        ]);

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }
}
