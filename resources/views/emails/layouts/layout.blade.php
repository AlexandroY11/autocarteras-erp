<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', 'AutoCarteras Cali')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5;
            color: #18181b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        }
        .header {
            background: #111111;
            padding: 28px 40px;
            text-align: center;
        }
        .header img {
            height: 52px;
            width: auto;
        }
        .badge {
            display: inline-block;
            margin-top: 14px;
            background: #F28C28;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 999px;
        }
        .content {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 8px;
        }
        .intro {
            font-size: 15px;
            color: #52525b;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .order-card {
            background: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 28px;
        }
        .order-card-header {
            background: #111111;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .order-number {
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .order-status {
            font-size: 12px;
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 999px;
            background: #F28C28;
            color: #fff;
        }
        .order-card-body {
            padding: 20px;
        }
        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e4e4e7;
            font-size: 14px;
        }
        .order-row:last-child {
            border-bottom: none;
        }
        .order-row .label {
            color: #71717a;
            font-weight: 500;
        }
        .order-row .value {
            color: #111111;
            font-weight: 600;
            text-align: right;
        }
        .highlight-box {
            border-left: 4px solid #F28C28;
            background: #fff8f0;
            border-radius: 0 8px 8px 0;
            padding: 14px 18px;
            margin-bottom: 28px;
            font-size: 14px;
            color: #52525b;
            line-height: 1.6;
        }
        .highlight-box strong {
            color: #c45f00;
        }
        .btn {
            display: inline-block;
            background: #F28C28;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            margin-bottom: 28px;
        }
        .footer {
            background: #fafafa;
            border-top: 1px solid #e4e4e7;
            padding: 24px 40px;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #a1a1aa;
            line-height: 1.7;
        }
        .footer strong {
            color: #52525b;
        }
        @media (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; }
            .content { padding: 28px 24px; }
            .header { padding: 22px 24px; }
            .footer { padding: 20px 24px; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <img src="https://autocarterascali.com/wp-content/uploads/2026/06/logo.png" alt="AutoCarteras Cali">
        @hasSection('badge')
            <div><span class="badge">@yield('badge')</span></div>
        @endif
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        <p>
            <strong>AutoCarteras Cali</strong><br>
            Fabricación de carteras para vehículos en fibra de vidrio<br>
            Cali, Valle del Cauca · Colombia<br><br>
            Este correo fue generado automáticamente, por favor no responda a este mensaje.
        </p>
    </div>
</div>
</body>
</html>