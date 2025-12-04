<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            max-height: 120px;
            width: auto;
            display: inline-block;
        }

        .content {
            font-size: 16px;
            white-space: pre-line;
        }
    </style>
</head>

<body>
    <div class="container">
        @if(isset($logo) && $logo)
            <div class="logo">
                <img src="{{ $message->embed(storage_path('app/public/' . $logo)) }}" alt="Logo">
            </div>
        @endif

        <div class="content">
            @if(isset($emailMessage) && $emailMessage)
                {!! nl2br(e($emailMessage)) !!}
            @endif
        </div>
    </div>
</body>

</html>