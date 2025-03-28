<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTypeController extends Controller
{
    /**
     * Show the user type selection form.
     *
     * @return \Illuminate\View\View
     */
    public function showUserTypeForm()
    {
        return view('auth.user-type');
    }

    /**
     * Update the user's type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserType(Request $request)
    {
        $request->validate([
            'user_type' => ['required', 'string', 'in:student,teacher'],
        ]);

        $user = Auth::user();
        $user->user_type = $request->user_type;
        $user->save();

        return redirect()->intended('/');
    }
}
