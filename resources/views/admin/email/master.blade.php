<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Password Reset</title>
</head>
<body style="background:#f6f6f6; margin:0; padding:0;">
    <div>
        <table width="600" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="border:1px solid #dddddd;">
            <!-- Body -->
            <tr>
                <td style="padding:20px; font-family:Arial, sans-serif; font-size:16px; color:#4a5a5a;">
                    <p>Hello Admin,</p>

                    <p>You are receiving this email because we received a password reset request for your account.</p>

                    <p style="text-align:center; margin:30px 0;">
                        <a href="{{ $link }}" style="background-color:#0b5aa4; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block;">
                            Reset Password
                        </a>
                    </p>

                    <p>If you did not request a password reset, no further action is required.</p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="background-color:#e5ebeb; color:#4a5a5a; font-size:12px; font-family:Arial, sans-serif; text-align:center; padding:10px;">
                    Powered by <a href="{{ url('/') }}" style="color:#0b5aa4; text-decoration:none;">{{ config('app.name', 'Dotsquares') }}</a>.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
