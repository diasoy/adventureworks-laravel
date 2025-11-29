<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Simple authentication - untuk demo purposes
        // Username: admin, Password: admin123
        // Username: executive, Password: exec123
        if (($credentials['username'] === 'admin' && $credentials['password'] === 'admin123') ||
            ($credentials['username'] === 'executive' && $credentials['password'] === 'exec123')) {
            
            Session::put('user', $credentials['username']);
            Session::put('logged_in', true);
            
            return redirect()->route('dashboard.sales-overview')
                           ->with('success', 'Login berhasil! Selamat datang di AdventureWorks Analytics.');
        }

        return back()->withErrors([
            'login' => 'Username atau password salah.'
        ])->withInput();
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}
