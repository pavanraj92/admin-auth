<?php

namespace admin\admin_auth\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use admin\admin_auth\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use admin\admin_auth\Mail\ForgotPasswordMail;

class ForgotPasswordController extends Controller
{
    public function forgotPassword()
    {
        return view('admin::admin.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();

        if (!$admin) {
            return back()->withErrors(['error' => 'We cannot find a user with this email.'])->withInput();
        }

        $token = Str::random(64);

        DB::table('admin_password_resets')->where('email', '=', $validated['email'])->delete();
        DB::table('admin_password_resets')->insert([
            'email' => $validated['email'],
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $admin->token = $token;
        Mail::to($admin->email)->send(new ForgotPasswordMail($admin));

        return redirect()->route('admin.forgotPassword')->with(['success' => 'We have emailed you a password reset link.']);
    }

}