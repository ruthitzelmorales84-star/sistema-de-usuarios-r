<?php

namespace App\Http\Controllers;

use App\Mail\AdminVerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['email'] = strtolower(trim($credentials['email']));

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        $user = Auth::user();
        $intendedUrl = route('home');

        if ($user->role === 'admin') {
            Auth::logout();
            $code = (string) random_int(100000, 999999);

            $request->session()->put('admin_2fa_code', $code);
            $request->session()->put('admin_2fa_user_id', $user->id);
            $request->session()->put('admin_2fa_expires_at', now()->addMinutes(5)->timestamp);
            $request->session()->put('admin_2fa_intended_url', $intendedUrl);
            $request->session()->forget(['admin_2fa_verified', 'module_intended_url']);

            Mail::to($user->email)->send(new AdminVerificationCodeMail($code, $user->name));

            return redirect()->route('admin.2fa.challenge');
        }

        $request->session()->regenerate();
        $request->session()->forget([
            'admin_2fa_code',
            'admin_2fa_user_id',
            'admin_2fa_expires_at',
            'admin_2fa_verified',
            'admin_2fa_intended_url',
            'module_intended_url',
        ]);

        return redirect()->to($intendedUrl);
    }

    public function showTwoFactorChallenge(Request $request)
    {
        $userId = $request->session()->get('admin_2fa_user_id');
        $expiresAt = (int) $request->session()->get('admin_2fa_expires_at', 0);

        if (! $userId || $expiresAt < now()->timestamp) {
            $request->session()->forget([
                'admin_2fa_code',
                'admin_2fa_user_id',
                'admin_2fa_expires_at',
                'admin_2fa_intended_url',
                'module_intended_url',
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'La sesión de verificación expiró. Inicia sesión nuevamente.',
            ]);
        }

        return view('auth.verify-admin');
    }

    public function verifyTwoFactor(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = (int) $request->session()->get('admin_2fa_user_id');
        $expectedCode = $request->session()->get('admin_2fa_code');
        $expiresAt = (int) $request->session()->get('admin_2fa_expires_at', 0);

        if (! $userId || $expiresAt < now()->timestamp || ! $expectedCode) {
            return redirect()->route('login')->withErrors([
                'email' => 'El código expiró. Inicia sesión nuevamente.',
            ]);
        }

        if (! hash_equals((string) $expectedCode, trim($data['code']))) {
            return back()->withErrors(['code' => 'El código de verificación no es válido.']);
        }

        $user = User::findOrFail($userId);
        Auth::login($user);
        $request->session()->regenerate();

        $intendedUrl = $request->session()->get('admin_2fa_intended_url', route('home'));
        $request->session()->forget([
            'admin_2fa_code',
            'admin_2fa_user_id',
            'admin_2fa_expires_at',
            'admin_2fa_intended_url',
        ]);
        $request->session()->put('admin_2fa_verified', true);

        return redirect()->to($intendedUrl);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget([
            'admin_2fa_code',
            'admin_2fa_user_id',
            'admin_2fa_expires_at',
            'admin_2fa_verified',
            'admin_2fa_intended_url',
        ]);

        return redirect()->route('login');
    }
}
