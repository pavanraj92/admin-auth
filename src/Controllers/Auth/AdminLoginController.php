<?php

namespace admin\admin_auth\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use admin\admin_auth\Models\Admin;
use admin\admin_auth\Services\OtpService;

class AdminLoginController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

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
            $admin = Auth::guard('admin')->user();
            
            // Generate and send OTP
            $this->otpService->generateAndSendOtp($admin, 'login');
            
            // Store admin ID in session for OTP verification
            session(['otp_admin_id' => $admin->id]);
            // Persist remember preference across OTP step
            session(['otp_remember' => $remember]);
            
            // Logout the admin temporarily
            Auth::guard('admin')->logout();
            
            return redirect()->route('admin.otp.verify')->with('success', 'OTP sent to your email. Please check your inbox.');
        }

        // If login failed, check if the account exists and is inactive to show a precise message
        $admin = Admin::where('email', $request->input('email'))->first();
        if ($admin && Hash::check($request->input('password'), $admin->password) && (int)($admin->status) !== 1) {
            return back()->withErrors(['error' => 'Your account is inactive. Please contact the administrator to activate it.']);
        }

        return back()->withErrors(['error' => 'The email or password you entered is incorrect. Please try again.']);
    }

    public function showOtpVerificationForm()
    {
        if (!session('otp_admin_id')) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Please login first.']);
        }

        $admin = Admin::find(session('otp_admin_id'));
        if (!$admin) {
            session()->forget('otp_admin_id');
            return redirect()->route('admin.login')->withErrors(['error' => 'Invalid session. Please login again.']);
        }

        // Get current valid OTP to calculate remaining time
        $otp = $this->otpService->getValidOtp($admin->id, 'login');
        if (!$otp) {
            return redirect()->route('admin.login')->withErrors(['error' => 'OTP expired. Please login again.']);
        }

        $remainingSeconds = max(0, now()->diffInSeconds($otp->expires_at, false));

        // Provide absolute timestamps to avoid client/server clock drift
        $serverNow = now()->timestamp; // seconds
        $resendAllowedAt = $otp->created_at->addSeconds(60)->timestamp; // seconds

        return view('admin::admin.auth.otp-verify', compact('admin', 'remainingSeconds', 'serverNow', 'resendAllowedAt'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $adminId = session('otp_admin_id');
        if (!$adminId) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Session expired. Please login again.']);
        }

        $otpCode = $request->input('otp_code');
        
        if ($this->otpService->verifyOtp($adminId, $otpCode, 'login')) {
            // Clear OTP session
            $remember = (bool) session('otp_remember', false);
            session()->forget('otp_admin_id');
            session()->forget('otp_remember');
            
            // Login the admin
            $admin = Admin::find($adminId);
            Auth::guard('admin')->login($admin, $remember);
            
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Login successful!');
        }

        return back()->withErrors(['error' => 'Invalid or expired OTP. Please try again.']);
    }

    public function resendOtp(Request $request)
    {
        $adminId = session('otp_admin_id');
        if (!$adminId) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired. Please login again.'], 401);
            }
            return redirect()->route('admin.login')->withErrors(['error' => 'Session expired. Please login again.']);
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            session()->forget('otp_admin_id');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid session. Please login again.'], 401);
            }
            return redirect()->route('admin.login')->withErrors(['error' => 'Invalid session. Please login again.']);
        }

        try {
            $this->otpService->resendOtp($admin, 'login');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'OTP resent to your email.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to resend OTP. Please try again.'], 500);
            }
            return back()->withErrors(['error' => 'Failed to resend OTP. Please try again.']);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        return redirect()->route('admin.login');
    }
}
