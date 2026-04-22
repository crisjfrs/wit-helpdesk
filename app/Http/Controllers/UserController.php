<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $role = $request->get('role', 'all');
        $query = User::query();

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15);

        return view('users.index', compact('users', 'role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['telegram_chat_id'] = trim((string) ($data['telegram_chat_id'] ?? '')) ?: null;
        $data['telegram_notifications_enabled'] = $request->boolean('telegram_notifications_enabled');

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telegram_chat_id' => ['nullable', 'string', 'max:30', 'regex:/^-?\\d+$/'],
            'telegram_notifications_enabled' => 'nullable|boolean',
        ];

        // Admin can update role
        if (auth()->user()->isAdmin() && $user->id !== auth()->id()) {
            $rules['role'] = 'required|in:admin,teknisi,user';
        }

        $data = $request->validate($rules);
        $data['telegram_chat_id'] = trim((string) ($data['telegram_chat_id'] ?? '')) ?: null;
        $data['telegram_notifications_enabled'] = $request->boolean('telegram_notifications_enabled');

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'required|string|min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(User $user)
    {
        $this->authorize('toggleActive', $user);

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User berhasil {$status}.");
    }
}
