<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #ffffff;
            padding: 30px 40px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .header img {
            max-width: 180px;
            height: auto;
        }
        .content {
            padding: 40px;
            line-height: 1.6;
            color: #4a5568;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px 40px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f7f9; padding: 20px 0;">
        <tr>
            <td align="center">
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <img src="{{ asset('images/logo.png') }}" alt="GDPS Logo">
                    </div>

                    <!-- Content -->
                    <div class="content">
                        @yield('content')
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p>&copy; {{ date('Y') }} PT Garuda Daya Pratama Sejahtera. All rights reserved.</p>
                        <p>This is an automated message from the GDPS ERP System. Please do not reply to this email.</p>
                        <p style="margin-top: 15px; font-style: italic;">
                            The information contained in this email and any attachments is confidential and may be subject to legal privilege. It is intended solely for the use of the individual or entity to whom it is addressed.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
