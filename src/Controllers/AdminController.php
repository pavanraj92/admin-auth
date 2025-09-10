<?php

namespace admin\admin_auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\admin_auth\Requests\ProfileRequest;
use admin\admin_auth\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function viewProfile()
    {
        $admin = \DB::table('admins')->find(auth('admin')->user()->id);
        return view('admin::admin.profile.view', compact('admin'));
    }

    public function profileUpdate(ProfileRequest $request)
    {
        try {
            $admin = auth('admin')->user();

            $admin->first_name   = $request->first_name ?? '';
            $admin->last_name    = $request->last_name ?? '';
            // $admin->email        = $request->email ?? '';
            $admin->website_name = $request->website_name ?? '';
    
            $admin->save();
    
            return redirect()->back()->with('success', 'Profile updated successfully.');
    
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function viewChangePassword()
    {
        $admin = \DB::table('admins')->find(auth('admin')->user()->id);
        return view('admin::admin.change_password.view', compact('admin'));
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
        try {
            $user = auth('admin')->user();

            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}