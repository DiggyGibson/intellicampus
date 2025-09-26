{{-- resources/views/emails/admissions/generic.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Admission Notification' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .content {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .important {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if(config('app.logo'))
                <img src="{{ config('app.logo') }}" alt="{{ config('app.name') }}" class="logo">
            @else
                <h2>{{ config('app.name') }}</h2>
            @endif
            <p style="margin: 0; color: #6c757d;">Office of Admissions</p>
        </div>

        <div class="content">
            {!! nl2br(e($messageContent)) !!}
        </div>

        @if(isset($portalLink))
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $portalLink }}" class="button">View Application Portal</a>
        </div>
        @endif

        <div class="footer">
            <p><strong>{{ config('app.name') }} - Office of Admissions</strong></p>
            <p>
                {{ config('admissions.address', '123 University Avenue') }}<br>
                {{ config('admissions.city', 'Monrovia') }}, {{ config('admissions.country', 'Liberia') }}<br>
                Phone: {{ config('admissions.phone', '+231 77 123 4567') }}<br>
                Email: admissions@{{ parse_url(config('app.url'), PHP_URL_HOST) }}
            </p>
            <p style="font-size: 0.8em; color: #adb5bd;">
                This is an automated message. Please do not reply directly to this email.<br>
                If you need assistance, please contact our admissions office.
            </p>
            @if(isset($applicationNumber))
            <p style="font-size: 0.8em; color: #adb5bd;">
                Reference: {{ $applicationNumber }}<br>
                Generated: {{ now()->format('F d, Y \a\t g:i A') }}
            </p>
            @endif
        </div>
    </div>
</body>
</html>