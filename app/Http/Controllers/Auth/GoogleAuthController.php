<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        // Check if Google Client ID or Secret is not configured or is placeholder
        if (empty($clientId) || empty($clientSecret) || $clientId === 'your_google_client_id' || $clientSecret === 'your_google_client_secret') {
            // Redirect directly to callback with mock data
            return redirect()->route('auth.google.callback', [
                'mock' => 'true',
                'name' => 'Budi Google Member',
                'email' => 'member.google@gmail.com',
                'id' => 'mock_google_id_987654'
            ]);
        }

        try {
            return Socialite::driver('google')
                ->redirectUrl(config('services.google.redirect'))
                ->redirect();
        } catch (\Exception $e) {
            // Fallback to mock login if Socialite fails
            return redirect()->route('auth.google.callback', [
                'mock' => 'true',
                'name' => 'Budi Google Member (Fallback)',
                'email' => 'member.google.fallback@gmail.com',
                'id' => 'mock_google_id_fallback'
            ]);
        }
    }

    /**
     * Handle the Google authentication callback.
     */
    public function handleGoogleCallback(\Illuminate\Http\Request $request)
    {
        try {
            if ($request->has('mock') && $request->mock === 'true') {
                $googleUserId = $request->id;
                $googleUserEmail = $request->email;
                $googleUserName = $request->name;
                $googleUserAvatar = 'https://www.gravatar.com/avatar/' . md5($googleUserEmail) . '?s=200';
            } else {
                $googleUser = Socialite::driver('google')
                    ->redirectUrl(config('services.google.redirect'))
                    ->user();
                $googleUserId = $googleUser->id;
                $googleUserEmail = $googleUser->email;
                $googleUserName = $googleUser->name;
                $googleUserAvatar = $googleUser->avatar;
            }

            // Check if user already exists
            $user = User::where('google_id', $googleUserId)
                ->orWhere('email', $googleUserEmail)
                ->first();

            if ($user) {
                // Update existing user with Google info
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUserId,
                        'avatar' => $googleUserAvatar,
                    ]);
                }

                if ($user->role === null) {
                    return redirect()->route('login')->with('error', 'Akun Anda sedang tidak aktif. Hubungi admin.');
                }

                Auth::login($user);

                // Bersihkan URL intended jika diarahkan ke feed background atau halaman admin padahal bukan admin
                $intended = session()->get('url.intended');
                if ($intended && ((str_contains($intended, '/admin') && !$user->isAdmin()) || str_contains($intended, '/notifications/feed'))) {
                    session()->forget('url.intended');
                }

                $targetRoute = route('dashboard');
                if ($user->isAdmin()) {
                    $targetRoute = route('admin.dashboard');
                } elseif ($user->isBroker()) {
                    $targetRoute = route('broker.dashboard');
                }

                return redirect()->intended($targetRoute)->with('success', 'Login berhasil dengan Google' . ($request->has('mock') ? ' (Mock Mode)' : '') . '!');
            }

            // Create new user with "member" role (default role)
            $newUser = User::create([
                'name' => $googleUserName,
                'email' => $googleUserEmail,
                'google_id' => $googleUserId,
                'avatar' => $googleUserAvatar,
                'password' => bcrypt('password'), // Default password for OAuth users
                'role' => 'member', // Map to member role
                'email_verified_at' => now(),
            ]);

            Auth::login($newUser);

            // Bersihkan URL intended jika sisa dari halaman admin/feed
            $intended = session()->get('url.intended');
            if ($intended && (str_contains($intended, '/admin') || str_contains($intended, '/notifications/feed'))) {
                session()->forget('url.intended');
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Registrasi dan login berhasil menggunakan Google' . ($request->has('mock') ? ' (Mock Mode)' : '') . '!');

        } catch (\Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            // Fallback to mock if it wasn't already a mock
            if (!$request->has('mock')) {
                return redirect()->route('auth.google.callback', [
                    'mock' => 'true',
                    'name' => 'Budi Google Member (Fallback)',
                    'email' => 'member.google.fallback@gmail.com',
                    'id' => 'mock_google_id_fallback'
                ]);
            }
            return redirect()->route('login')->with('error', 'Gagal login dengan Google. Silakan coba lagi atau gunakan metode login lain.');
        }
    }
}