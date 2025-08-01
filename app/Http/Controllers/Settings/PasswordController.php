<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request): Response
    {
        $user = Auth::user();
        if($user->role == 'admin'){
            return Inertia::render('admin/settings/Password', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => $request->session()->get('status'),
            ]);

        }
        else{
            return Inertia::render('frontend/settings/Password', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => $request->session()->get('status'),
            ]);
        }

    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
