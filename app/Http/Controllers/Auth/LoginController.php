<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('content.authentications.auth-login-basic');
    }

    public function login(Request $request)
    {
        $credentials = [
            'email'    => $request->email,
            'password' => $request->senha,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            AuditLog::registrar(action: 'login', userId: Auth::id());
            return redirect('/');
        }

        return back()->with('error', 'Email ou senha incorretos.');
    }

    public function logout(Request $request)
    {
        AuditLog::registrar(action: 'logout', userId: Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
