<?php

namespace admin\admin_auth\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use admin\admin_auth\Models\Admin;

class AdminLoginController extends Controller
{
     public function showLoginForm()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin::admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        // Ensure only active admins (status = 1) can log in
        $credentials['status'] = 1;
        $remember = $request->has('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // If login failed, check if the account exists and is inactive to show a precise message
        $admin = Admin::where('email', $request->input('email'))->first();
        if ($admin && Hash::check($request->input('password'), $admin->password) && (int)($admin->status) !== 1) {
            return back()->withErrors(['error' => 'Your account is inactive. Please contact the administrator to activate it.']);
        }

        return back()->withErrors(['error' => 'The email or password you entered is incorrect. Please try again.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        return redirect()->route('admin.login');
    }
}
