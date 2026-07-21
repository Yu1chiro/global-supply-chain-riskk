<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

// Kelas GoogleController: google controller
class GoogleController extends Controller
{
    
    // redirect
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    
    // callback
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first();
        $isNewUser = false;

        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                
                $user = User::create([
                    'name' => $googleUser->getName() ?: $googleUser->getNickname(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => null,
                ]);
                $isNewUser = true;
            }
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        ActivityLog::record($isNewUser ? 'register.google' : 'login.google', null, $user->id);

        return redirect()->intended(route('dashboard'));
    }
}
