<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mail Template')</title>
    <style>
        body {
            background: #f4f6f8;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .mail-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .mail-header {
            background: #333;
            color: #fff;
            padding: 32px 32px 24px 32px;
            text-align: center;
        }

        .mail-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }

        .mail-body {
            padding: 32px;
            color: #333;
        }

        .mail-body h2 {
            margin-top: 0;
            font-size: 1.25rem;
            color: #333;
        }

        .mail-footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 20px 32px;
            font-size: 0.95rem;
        }

        .btn {
            display: inline-block;
            background: #007bff;
            color: #fff;
            padding: 12px 28px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 24px;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="mail-container">
        <div class="mail-header">
            <table width="100%" cellpadding="0" cellspacing="0"
                style="background:#333; padding:20px; text-align:center;">
                <tr>
                    <td align="center">
                       <img src="{{ config('GET.main_logo') ? url(Storage::url(config('GET.main_logo'))) : asset('images/logo.png') }}" alt="Dotsquares" />
                    </td>
                </tr>
            </table>
        </div>
        <div class="mail-body">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff; padding:20px;">
                <tr>
                    <td
                        style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#4a5a5a; padding-bottom: 15px;">
                        {{-- @hasSection('content')
                            @yield('content') --}}
                        @if(isset($template))
                            {!! $template !!}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="mail-footer">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center"
                        style="background-color:#333; color:#fff; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:center; padding:10px;">
                        Powered by <a href="{{ url('/') }}">{{ env('APP_NAME', 'Dotsquares') }}</a>.
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
