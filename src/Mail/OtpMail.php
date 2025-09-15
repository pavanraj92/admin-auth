<?php

namespace admin\admin_auth\Mail;

use admin\admin_auth\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $otpCode;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $admin, string $otpCode)
    {
        $this->admin = $admin;
        $this->otpCode = $otpCode;
    }

    /**
     * Build the message using DB template first with fallback HTML, same pattern as ForgotPasswordMail
     */
    public function build()
    {
        try {
            if (Schema::hasTable('emails')) {
                $emailTemplate = DB::table('emails')->where('slug', 'admin_login_otp')->first(['subject', 'description']);
            } else {
                $emailTemplate = null;
            }
        } catch (\Exception $e) {
            $emailTemplate = null;
        }

        $subject = $emailTemplate?->subject ?? 'Your Admin Login OTP Code - ' . env('APP_NAME');
        $content = $emailTemplate?->description ?? '
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color:#333; margin-bottom:8px;">Two-Factor Authentication</h2>
                <p style="color:#666;">Use the verification code below to complete your login for <strong>%USER_EMAIL%</strong>.</p>
            </div>
            <div style="background: #f8f9fa; border-radius: 8px; padding: 30px; text-align: center; margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 20px; font-size: 18px;">Verification Code</h3>
                            <div style="background: #007bff; color: white; font-size: 32px; font-weight: bold; letter-spacing: 8px; padding: 20px; border-radius: 8px; display: inline-block; min-width: 240px;">
                    %OTP_CODE%
                </div>
                <p style="color: #666; margin-top: 15px; font-size: 14px;">
                    This code will expire in <strong>5 minutes</strong>.
                </p>
            </div>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                <p style="color: #856404; margin: 0; font-size: 14px;">
                    <strong>Security Notice:</strong> If you didn\'t request this code, please ignore this email and consider changing your password.
                </p>
            </div>
        </div>

        <div style="text-align: center; color: #666; font-size: 14px;">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>For security reasons, this code can only be used once and will expire in 5 minutes.</p>
        </div>
        </div>
        <p>%EMAIL_FOOTER%</p>
        ';


        // Replace placeholders
        $content = str_replace('%EMAIL_FOOTER%', config('GET.email_footer_text', ''), $content);
        $subject = str_replace('%APP_NAME%', env('APP_NAME'), $subject);
        $content = str_replace('%APP_NAME%', env('APP_NAME'), $content);
        $content = str_replace('%USER_EMAIL%', e($this?->admin?->email), $content);
        $content = str_replace('%OTP_CODE%', e($this->otpCode), $content);

        return $this->subject($subject)
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->replyTo(env('MAIL_FROM_ADDRESS'))
            ->view('admin::admin.email.master')
            ->with(['template' => $content]);
    }
}