<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider) 
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $finduser = User::where('provider_id', $socialiteUser->id)->first();

            if($finduser){
                Auth::login($finduser);
                
                return redirect('/home');
            }
            else {
                $validator = Validator::make(
                    ['email' => $socialiteUser->getEmail()],
                    ['email' => 'unique:users,email'],
                    ['email.unique' => 'Could not log in. Maybe you used a different login method?'],
                );
        
                if ($validator->fails())
                {
                    return redirect('/login')->withErrors($validator);
                }
        
                $user = User::firstOrCreate([
                    'name' => $socialiteUser->getName(),
                    'email' => $socialiteUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialiteUser->getId(),
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
    
                return redirect('/');
            }
        } catch (\Exception $e) {
            return redirect('/login');
        }
    }
}
