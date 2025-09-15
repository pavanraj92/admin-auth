@extends('admin::admin.email.master')

@section('content')
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: #333; margin-bottom: 10px;">Two-Factor Authentication</h2>
            <p style="color: #666; font-size: 16px;">Your login verification code for <strong>{{ $admin->email }}</strong></p>
        </div>

        <div style="background: #f8f9fa; border-radius: 8px; padding: 30px; text-align: center; margin-bottom: 30px;">
            <h3 style="color: #333; margin-bottom: 20px; font-size: 18px;">Verification Code</h3>
            <div style="background: #007bff; color: white; font-size: 32px; font-weight: bold; letter-spacing: 8px; padding: 20px; border-radius: 8px; display: inline-block; min-width: 240px;">
                {{ $otpCode }}
            </div>
            <p style="color: #666; margin-top: 15px; font-size: 14px;">
                This code will expire in <strong>5 minutes</strong>.
            </p>
        </div>

        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <p style="color: #856404; margin: 0; font-size: 14px;">
                <strong>Security Notice:</strong> If you didn't request this code, please ignore this email and consider changing your password.
            </p>
        </div>

        <div style="text-align: center; color: #666; font-size: 14px;">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>For security reasons, this code can only be used once and will expire in 5 minutes.</p>
        </div>
    </div>
@endsection
