<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'password.required' => 'يرجى إدخال كلمة المرور',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'مرحباً بك في نظام قوة الكوبرا');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة، يرجى المحاولة مرة أخرى.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'تم تسجيل الخروج بنجاح.');
    }
}
