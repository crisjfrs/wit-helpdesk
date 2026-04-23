<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telegram_chat_id' => ['nullable', 'string', 'max:30', 'regex:/^-?\\d+$/'],
            'telegram_notifications_enabled' => 'nullable|boolean',
        ]);

        $data['telegram_chat_id'] = trim((string) ($data['telegram_chat_id'] ?? '')) ?: null;
        $data['telegram_notifications_enabled'] = $request->boolean('telegram_notifications_enabled');

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
