<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user && $user->user_type === 'unknown') {
            return route('user-type.show');
        }

        return $this->redirectTo;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'redirectToGoogle', 'handleGoogleCallback',
            'redirectToFacebook', 'handleFacebookCallback',
            'redirectToTwitter', 'handleTwitterCallback'
        ]);
        $this->middleware('auth')->only('logout');
    }

    /**
     * Generic method to handle social login
     *
     * @param string $provider
     * @param object $socialUser
     * @return \App\Models\User
     */
    protected function handleSocialUser($provider, $socialUser)
    {
        // For Twitter, email might not be available
        $email = $socialUser->getEmail() ?? "{$socialUser->getId()}@{$provider}.user";

        // Try to find user by email or provider ID
        $user = User::where('email', $email)
            ->orWhere("{$provider}_id", $socialUser->getId())
            ->first();

        // If user doesn't exist, create a new one
        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $email,
                'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                'avatar' => $socialUser->getAvatar(),
                "{$provider}_id" => $socialUser->getId(),
                'user_type' => 'unknown', // Default user type for social logins
            ]);

            // Assign the "Registered" role to the newly registered user
            $registeredRole = Role::where('name', 'Registered')->first();
            if ($registeredRole) {
                $user->roles()->attach($registeredRole);
            }
        } else {
            // Update existing user with social data
            $user->update([
                "{$provider}_id" => $socialUser->getId(),
                'avatar' => $user->avatar ?? $socialUser->getAvatar(),
            ]);
        }

        return $user;
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            $user = $this->handleSocialUser('google', $socialUser);

            // Login the user
            Auth::login($user, true);

            return redirect($this->redirectTo);

        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleFacebookCallback()
    {
        try {
            $socialUser = Socialite::driver('facebook')->user();
            $user = $this->handleSocialUser('facebook', $socialUser);

            // Login the user
            Auth::login($user, true);

            return redirect($this->redirectTo);

        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Facebook authentication failed. Please try again.');
        }
    }

    /**
     * Redirect the user to the Twitter authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Obtain the user information from Twitter.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleTwitterCallback()
    {
        try {
            $socialUser = Socialite::driver('twitter')->user();
            $user = $this->handleSocialUser('twitter', $socialUser);

            // Login the user
            Auth::login($user, true);

            return redirect($this->redirectTo);

        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Twitter authentication failed. Please try again.');
        }
    }
}
