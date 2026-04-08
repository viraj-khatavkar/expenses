<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Response;

final class PasswordController extends Controller
{
    public function edit(): Response
    {
        return inertia('Account/ChangePassword');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $request->password,
        ]);

        return to_route('password.edit')->with('success', 'Password changed successfully.');
    }
}
