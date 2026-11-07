<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthViewController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle staff login and redirect based on role.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('username', $credentials['username'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        // Login user dengan web session guard (explicitly use 'web' guard)
        Auth::guard('web')->login($user);

        // Regenerate session untuk security
        $request->session()->regenerate();

        // Redirect based on role with intended destination
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang, Admin!');
        }

        return redirect()->intended(route('kasir.dashboard'))->with('success', 'Selamat datang di dashboard Kasir!');
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
