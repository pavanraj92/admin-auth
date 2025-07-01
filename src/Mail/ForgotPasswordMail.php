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
        return $this->subject('Reset Your Password - ' . env('APP_NAME'))
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->replyTo(env('MAIL_FROM_ADDRESS'))
            ->view('admin::admin.email.master')
            ->with(['link' => $link]);
    }
}
