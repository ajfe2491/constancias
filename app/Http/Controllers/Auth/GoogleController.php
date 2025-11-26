<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail(),
                'password' => Str::random(40),
            ],
        );

        if (! $user->hasAnyRole(['admin', 'user'])) {
            $defaultRole = Role::where('name', 'user')->first();

            if ($defaultRole) {
                $user->assignRole($defaultRole);
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}


