<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\LoginActivity;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
                'role' => $user->getRoleNames()->first()
            ], 201);
        }

        Auth::login($user);
        $this->recordLogin($request);
        return $this->redirectBasedOnRole($user);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $credentials['is_active'] = true;

        if (!Auth::attempt($credentials)) {
            // Check if user exists but is inactive
            $user = \App\Models\User::where('email', $request->email)->first();
            if ($user && !$user->is_active) {
                $error = 'Institutional access suspended for this identity.';
            } else {
                $error = 'Invalid credentials matrix.';
            }

            if ($request->wantsJson()) {
                return response()->json(['message' => $error], 401);
            }
            return back()->withErrors(['email' => $error]);
        }

        $user = Auth::user();
        $this->recordLogin($request);
        
        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
                'role' => $user->getRoleNames()->first()
            ]);
        }
        
        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole($user)
    {
        if ($user->hasRole('Admin') || $user->hasRole('Director')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }
    
    public function logout(Request $request)
    {
        // Mark logout time on the most recent login activity
        if ($request->user()) {
            LoginActivity::where('user_id', $request->user()->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first()
                ?->update(['logout_at' => now()]);

            $request->user()->tokens()->delete();
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    /**
     * Record login activity and update last_login_at timestamp.
     */
    protected function recordLogin(Request $request): void
    {
        $user = Auth::user();
        $userAgent = $request->userAgent();
        $parsed = LoginActivity::parseUserAgent($userAgent);

        // Record login activity
        LoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $parsed['device_type'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'login_at' => now(),
        ]);

        // Update last_login_at on user record
        $user->update(['last_login_at' => now()]);
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function processForgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset email dispatch failure: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $request->email,
            ]);

            return back()->withErrors([
                'email' => 'Unable to dispatch reset email via mail server (' . $e->getMessage() . '). Please verify institutional SMTP configuration.'
            ]);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'If this email belongs to an active institutional account, a password reset link has been dispatched to it.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function processResetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'            => Hash::make($password),
                    'must_change_password' => false,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password reset successfully. Please log in with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}
