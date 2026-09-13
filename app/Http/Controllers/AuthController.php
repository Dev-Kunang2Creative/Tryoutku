<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route($this->homeRouteFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route($this->homeRouteFor(Auth::user())))
                ->with('success', 'Selamat belajar!');
        }

        return back()->withErrors([
            'username' => 'Username atau kata sandi tidak cocok.',
        ])->onlyInput('username');
    }

    /**
     * The manager account lands in the question panel; the learner lands on
     * today's practice page.
     */
    private function homeRouteFor(User $user): string
    {
        return $user->isAdmin() ? 'admin.dashboard' : 'student.dashboard';
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda sudah keluar.');
    }
}
