<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        if ($request->has('password') && is_string($request->password)) {
            $request->merge([
                'password' => trim($request->password),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Pengecekan status persetujuan untuk Operator / Mitra Developer
            if ($user->approval_status === 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Pendaftaran akun Operator Anda masih dalam proses verifikasi oleh Super Admin. Mohon menunggu persetujuan.',
                ])->withInput();
            }

            if ($user->approval_status === 'rejected') {
                $reason = $user->company?->rejection_reason;
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Pendaftaran akun Operator Anda telah ditolak' . ($reason ? ': ' . $reason : '.'),
                ])->withInput();
            }

            $request->session()->regenerate();

            // Bersihkan URL intended jika mengarah ke background feed atau halaman admin padahal bukan admin
            $intended = session()->get('url.intended');
            if ($intended && ((str_contains($intended, '/admin') && !$user->isAdmin()) || str_contains($intended, '/notifications/feed'))) {
                session()->forget('url.intended');
            }

            // Redirect to broker portal if user is broker
            if ($user->isBroker()) {
                return redirect()->intended(route('broker.dashboard'));
            }

            // Redirect to admin dashboard if user is admin
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Redirect to peserta dashboard
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda berhasil logout.');
    }
}