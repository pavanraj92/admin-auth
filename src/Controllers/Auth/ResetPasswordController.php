<?php

namespace admin\admin_auth\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use admin\admin_auth\Models\Admin;
use admin\admin_auth\Requests\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request, $token)
    {
        $checkTokenExpired = DB::table('admin_password_resets')
                                    ->where('token','=', $token)
                                    ->where('created_at','>',Carbon::now()->subHours(2))
                                    ->first();

        if(empty($checkTokenExpired)) { 
            Session::flash('linked-expired', 'Reset password link is expired.'); 
        } 

        return view('admin::admin.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);

    }

    public function postResetPassword(ResetPasswordRequest $request)
    {        
        $checkTokenExpired = DB::table('admin_password_resets')
                ->where('token','=',$request->token)
                ->where('created_at','>',Carbon::now()->subHours(2))
                ->first();

        if(isset($checkTokenExpired)) {

            
            $userExists = Admin::where([ 'email'=>$request->email ])->first();
            if ($userExists) {            
                $admin = Admin::where('id', $userExists->id)->update(['password' => Hash::make($request->password)]);
                DB::table('admin_password_resets')->where('token','=',$request->token)->delete();
                $slug = DB::table('admins')->select('website_slug')->first();
                return redirect($slug->website_slug . '/admin/login')->with(['success' => "Password updated successfully. Please login here"]);
            } else {
                return  back()->withErrors(['email' => 'Something went wrong. Please try later']);
            }

        } else {
            
            return  back()->withErrors(['email' => 'Reset password link is expired.']);
        }
    }
}