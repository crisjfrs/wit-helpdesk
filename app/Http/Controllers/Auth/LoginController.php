<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Show the register form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $email = strtolower(trim($data['email']));
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('pending_registration', [
            'name' => $data['name'],
            'email' => $email,
            'password' => Crypt::encryptString($data['password']),
            'code' => $code,
            'expires_at' => now()->addMinutes(10)->toDateTimeString(),
        ]);

        $request->session()->forget('pending_verification_user_id');

        $sent = $this->sendVerificationCodeToEmail($data['name'], $email, $code);

        if (!$sent) {
            return redirect()->route('verification.notice')
                ->with('error', 'Kode verifikasi gagal dikirim. Data pendaftaran Anda belum disimpan sebagai akun. Silakan klik Kirim Ulang Kode.');
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Kode verifikasi sudah dikirim. Akun Anda akan dibuat setelah email berhasil diverifikasi.');
    }

    /**
     * Show email verification code form
     */
    public function showVerificationForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $pendingRegistration = $request->session()->get('pending_registration');

        if (!$pendingRegistration) {
            return redirect()->route('register')->with('error', 'Silakan daftar terlebih dahulu untuk melakukan verifikasi email.');
        }

        return view('auth.verify-email', ['email' => $pendingRegistration['email']]);
    }

    /**
     * Verify email with code
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $pendingRegistration = $request->session()->get('pending_registration');

        if (!$pendingRegistration) {
            return redirect()->route('register')->with('error', 'Sesi verifikasi telah habis. Silakan daftar ulang.');
        }

        if (!isset($pendingRegistration['code'], $pendingRegistration['expires_at']) || Carbon::parse($pendingRegistration['expires_at'])->isPast()) {
            return back()->withErrors([
                'code' => 'Kode verifikasi sudah kedaluwarsa. Silakan kirim ulang kode.',
            ]);
        }

        if ($request->code !== (string) $pendingRegistration['code']) {
            return back()->withErrors([
                'code' => 'Kode verifikasi tidak valid.',
            ])->withInput();
        }

        $email = strtolower(trim((string) ($pendingRegistration['email'] ?? '')));

        if (User::where('email', $email)->exists()) {
            $request->session()->forget('pending_registration');
            return redirect()->route('login')->with('error', 'Email ini sudah terdaftar. Silakan login.');
        }

        try {
            $password = Crypt::decryptString((string) ($pendingRegistration['password'] ?? ''));
        } catch (Throwable $e) {
            $request->session()->forget('pending_registration');
            return redirect()->route('register')->with('error', 'Data pendaftaran tidak valid. Silakan daftar ulang.');
        }

        $user = User::create([
            'name' => (string) ($pendingRegistration['name'] ?? ''),
            'email' => $email,
            'password' => $password,
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $request->session()->forget('pending_registration');
        $request->session()->forget('pending_verification_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Email berhasil diverifikasi dan akun berhasil dibuat. Selamat datang!');
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request)
    {
        $pendingRegistration = $request->session()->get('pending_registration');

        if (!$pendingRegistration) {
            return redirect()->route('register')->with('error', 'Sesi verifikasi telah habis. Silakan daftar ulang.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $pendingRegistration['code'] = $code;
        $pendingRegistration['expires_at'] = now()->addMinutes(10)->toDateTimeString();
        $request->session()->put('pending_registration', $pendingRegistration);

        $sent = $this->sendVerificationCodeToEmail(
            (string) ($pendingRegistration['name'] ?? 'User'),
            (string) ($pendingRegistration['email'] ?? ''),
            $code
        );

        if (!$sent) {
            return back()->with('error', 'Gagal mengirim ulang kode verifikasi. Periksa konfigurasi email, lalu coba lagi.');
        }

        return back()->with('success', 'Kode verifikasi baru sudah dikirim ke email Anda.');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials['email'] = strtolower(trim($credentials['email']));

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function sendVerificationCodeToEmail(string $name, string $email, string $code): bool
    {
        try {
            Mail::raw(
                "Halo {$name},\n\nKode verifikasi email Anda adalah: {$code}\nKode berlaku selama 10 menit.\n\nJika Anda tidak merasa melakukan pendaftaran, abaikan email ini.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Kode Verifikasi Email - WIT Helpdesk');
                }
            );

            return true;
        } catch (Throwable $e) {
            Log::error('Gagal mengirim email verifikasi.', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
