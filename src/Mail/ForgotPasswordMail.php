<?php

namespace admin\admin_auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

/**
 * This class used for frontend user as customer/home owner user
 */
class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $slug = \DB::table('admins')->select('website_slug')->first();
        $link = url(($slug ? $slug->website_slug . '/admin/reset-password' : 'admin/reset-password') . '/' . $this->user->token . '?email=' . $this->user->email);
        // return $this->subject('Reset Your Password - ' . env('APP_NAME'))
        //     ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
        //     ->replyTo(env('MAIL_FROM_ADDRESS'))
        //     ->view('admin::admin.email.master')
        //     ->with(['link' => $link]);

    
        try {
            if (\Schema::hasTable('emails')) {
                $emailTemplate = \DB::table('emails')->where('slug', 'password_reset')->first(['subject', 'description']);
            } else {
                $emailTemplate = null;
            }
        } catch (\Exception $e) {
            $emailTemplate = null;
        }
    
        $subject = $emailTemplate?->subject ?? 'Reset Your Password - ' . env('APP_NAME');
        $content = $emailTemplate?->description ?? '
            <p>Dear %USER_NAME%,</p>

            <p>We received a request to reset your password for your <strong>%APP_NAME%</strong> account. To proceed, please click the link below:</p>

            <p>%RESET_LINK%</p>

            <p>If you did not request a password reset, please ignore this email or contact our support team immediately.</p>

            <p>Best regards,<br />
            The %APP_NAME% Team<br />
            %EMAIL_FOOTER%</p>
        ';
    
        // Replace placeholders
        $content = str_replace('%EMAIL_FOOTER%', config('GET.email_footer_text', ''), $content);
        $subject = str_replace('%APP_NAME%', env('APP_NAME'), $subject);
        $content = str_replace('%APP_NAME%', env('APP_NAME'), $content);
        $resetLink = '<a href="' . $link . '" style="display:inline-block; background:#007bff; color:#fff; padding:12px 24px; text-decoration:none; border-radius:4px; font-weight:600;">Reset Password</a>';
        $content = str_replace('%RESET_LINK%', $resetLink, $content);
        $content = str_replace('%USER_NAME%', 'Admin', $content);
    
        return $this->subject($subject)
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->replyTo(env('MAIL_FROM_ADDRESS'))
            ->view('admin::admin.email.master')
            ->with(['template' => $content]);
    }
}