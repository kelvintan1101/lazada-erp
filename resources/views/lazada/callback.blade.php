<!DOCTYPE html>
<html>
<head>
    <title>Lazada Authorization</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
            color: #333;
            text-align: center;
        }
        .message-box {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }
        h2 {
            margin-top: 0;
            color: {{ $success ? '#2ecc71' : '#e74c3c' }};
        }
        .error {
            background-color: #fceaea;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2>{{ $success ? 'Authorization Successful' : 'Authorization Failed' }}</h2>
        
        @if($success)
            <p>Your Lazada account has been successfully connected!</p>
            <p>You can now close this window.</p>
        @else
            <p>There was a problem connecting your Lazada account.</p>
            <div class="error">
                <p>Error: {{ $error }}</p>
            </div>
            <p>This window will close in a few seconds.</p>
        @endif
    </div>

    <script>
        window.opener.postMessage({
            type: 'LAZADA_AUTH',
            success: {{ $success ? 'true' : 'false' }},
            @if($success)
            message: 'Authorization successful'
            @else
            error: '{{ addslashes($error) }}'
            @endif
        }, '*');
        
        setTimeout(() => window.close(), {{ $success ? 1000 : 5000 }});
    </script>
</body>
</html> 