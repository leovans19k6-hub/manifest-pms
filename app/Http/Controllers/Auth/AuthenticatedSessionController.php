<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Domain\Foundation\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AuthenticationService $authentication): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $authentication->attempt($credentials, $request->boolean('remember'));

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(AuthenticationService $authentication): RedirectResponse
    {
        $authentication->logout();

        return redirect()->route('login');
    }
}
