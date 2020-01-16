<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Socialite;
use App\User;

class SocialiteController extends Controller
{
    
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
    * Obtain the user information from given provider.
    *
    * @return \Illuminate\Http\Response
    */
    public function handleProviderCallback($provider)
    {
        $social_user = Socialite::driver($provider)->stateless()->user();    // return the Laravel Passport access token response
        $this->findOrCreate($social_user);
        $url = env('APP_FRONTEND_URL');
        return redirect("{$url}/auth/callback?token={$social_user->token}&provider=$provider");
    }
    public function findOrCreate($social_user)
    {
        try {
            User::firstOrFail()->where('email', $social_user->email);
        } catch (ModelNotFoundException $e) {
            User::create([
                "name" => $social_user->name,
                "email" => $social_user->email,
                "password" =>  Hash::make(Str::random(52)),
                "email_verified_at" => now()
            ]);
        }
    }
}