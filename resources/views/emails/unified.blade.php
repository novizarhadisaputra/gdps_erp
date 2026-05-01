<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification from GDPS' }}</title>
    <style>
        body {
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }

        .main {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-top: 40px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            background-color: #ffffff;
            padding: 32px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .header img {
            height: 48px;
            width: auto;
        }

        .content {
            padding: 40px;
            font-size: 16px;
            color: #1e293b;
        }

        .content h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 32px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            margin: 4px 0;
        }

        .confidentiality {
            margin-top: 24px;
            font-style: italic;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            line-height: 1.4;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <img src="https://career.garudapratama.com/img/banner/gdps_logo.png" alt="GDPS Logo">
            </div>
            <div class="content">
                {!! $body !!}
            </div>
            <div class="footer">
                <p><strong>PT Garuda Daya Pratama Sejahtera (GDPS)</strong></p>
                <p>Gedung Management Garuda City, Bandara Internasional Soekarno-Hatta</p>
                <p>Tangerang 15111, Indonesia</p>
                <div style="margin-top: 12px;">
                    <a href="https://garudapratama.com">Website</a> |
                    <a href="mailto:info@garudapratama.com">Contact Us</a>
                </div>
                <div class="confidentiality">
                    This email and any attachments are confidential and intended solely for the use of the individual or
                    entity to whom they are addressed. If you have received this email in error, please notify the
                    system manager. This message contains confidential information and is intended only for the
                    individual named.
                </div>
            </div>
        </div>
    </div>
</body>

</html>
