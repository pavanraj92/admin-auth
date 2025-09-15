<?php

namespace admin\admin_auth\Services;

use admin\admin_auth\Models\Admin;
use admin\admin_auth\Models\AdminOtp;
use admin\admin_auth\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate and send OTP to admin
     */
    public function generateAndSendOtp(Admin $admin, string $type = 'login'): AdminOtp
    {
        // Invalidate any existing OTPs for this admin
        $this->invalidateExistingOtps($admin->id, $type);

        // Generate new OTP
        $otpCode = $this->generateOtpCode();
        $expiresAt = now()->addMinutes(5); // 5 minutes expiration

        // Create OTP record
        $otp = AdminOtp::create([
            'admin_id' => $admin->id,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'type' => $type,
        ]);

        // Send OTP via email
        $this->sendOtpEmail($admin, $otpCode);

        return $otp;
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(int $adminId, string $otpCode, string $type = 'login'): bool
    {
        $otp = AdminOtp::where('admin_id', $adminId)
                      ->where('otp_code', $otpCode)
                      ->where('type', $type)
                      ->where('is_used', false)
                      ->where('expires_at', '>', now())
                      ->first();

        if (!$otp) {
            return false;
        }

        // Mark OTP as used
        $otp->markAsUsed();

        return true;
    }

    /**
     * Get valid OTP for admin
     */
    public function getValidOtp(int $adminId, string $type = 'login'): ?AdminOtp
    {
        return AdminOtp::where('admin_id', $adminId)
                      ->where('type', $type)
                      ->where('is_used', false)
                      ->where('expires_at', '>', now())
                      ->latest()
                      ->first();
    }

    /**
     * Check if admin has valid OTP
     */
    public function hasValidOtp(int $adminId, string $type = 'login'): bool
    {
        return $this->getValidOtp($adminId, $type) !== null;
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Admin $admin, string $type = 'login'): AdminOtp
    {
        return $this->generateAndSendOtp($admin, $type);
    }

    /**
     * Generate 6-digit OTP code
     */
    private function generateOtpCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP via email
     */
    private function sendOtpEmail(Admin $admin, string $otpCode): void
    {
        try {
            Mail::to($admin->email)->send(new OtpMail($admin, $otpCode));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            throw new \Exception('Failed to send OTP. Please try again.');
        }
    }

    /**
     * Invalidate existing OTPs for admin
     */
    private function invalidateExistingOtps(int $adminId, string $type): void
    {
        AdminOtp::where('admin_id', $adminId)
                ->where('type', $type)
                ->where('is_used', false)
                ->update(['is_used' => true]);
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpiredOtps(): int
    {
        return AdminOtp::cleanupExpired();
    }

    /**
     * Get OTP time remaining for admin
     */
    public function getOtpTimeRemaining(int $adminId, string $type = 'login'): ?int
    {
        $otp = $this->getValidOtp($adminId, $type);
        return $otp ? $otp->getTimeRemaining() : null;
    }

    /**
     * Get formatted time remaining
     */
    public function getFormattedTimeRemaining(int $adminId, string $type = 'login'): ?string
    {
        $otp = $this->getValidOtp($adminId, $type);
        return $otp ? $otp->getTimeRemainingFormatted() : null;
    }
}
