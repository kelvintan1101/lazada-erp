<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lazada Authorization</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .content {
            max-width: 700px;
            margin: 50px auto;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        #loading {
            text-align: center;
            margin: 30px 0;
        }
        #error-details {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h2>Lazada Authorization</h2>
                </div>
                <div class="card-body">
                    <div id="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Processing authorization code...</p>
                    </div>
                    
                    <div id="success" style="display:none">
                        <div class="alert alert-success">
                            <h4>Authorization successful!</h4>
                            <p>The window will close automatically in a moment.</p>
                        </div>
                    </div>
                    
                    <div id="error" style="display:none">
                        <div class="alert alert-danger">
                            <h4>Authorization failed</h4>
                            <p id="error-message"></p>
                            <button class="btn btn-sm btn-outline-primary mt-2" type="button" onclick="toggleErrorDetails()">Show Details</button>
                        </div>
                        <div id="error-details">
                            <pre id="error-data"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const code = "{{ $data['code'] ?? '' }}";
        
        function toggleErrorDetails() {
            const details = document.getElementById('error-details');
            details.style.display = details.style.display === 'none' ? 'block' : 'none';
        }
        
        async function processCode() {
            if (!code) {
                showError('No authorization code provided');
                return;
            }
            
            try {
                // API endpoint - this is important for signature generation
                const apiEndpoint = '/auth/token/create';
                
                // Get the current timestamp in milliseconds
                const timestamp = Date.now();
                
                // Prepare the request parameters
                const params = {
                    app_key: '{{ env('LAZADA_APP_KEY') }}',
                    code: code,
                    grant_type: 'authorization_code',
                    redirect_uri: 'https://techsolution11.online/lazada/debug-callback',
                    timestamp: timestamp,
                    sign_method: 'sha256',
                };
                
                // Directly process the token exchange on the client side
                const response = await fetch('/lazada/simple-token/' + code, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.data && result.data.access_token) {
                    // Success - token received
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('success').style.display = 'block';
                    
                    // Message the parent window
                    if (window.opener) {
                        window.opener.postMessage({
                            type: 'LAZADA_AUTH',
                            success: true,
                            message: 'Authorization successful',
                            data: result.data
                        }, '*');
                    }
                    
                    // Close this window after a delay
                    setTimeout(() => window.close(), 2000);
                } else {
                    // Error - no token received
                    let errorMessage = 'Failed to get access token';
                    if (result.data && result.data.message) {
                        errorMessage = result.data.message;
                    } else if (result.error) {
                        errorMessage = result.error;
                    }
                    
                    showError(errorMessage, result);
                }
            } catch (error) {
                showError('Error processing request: ' + error.message);
            }
        }
        
        function showError(message, details = null) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            document.getElementById('error-message').textContent = message;
            
            if (details) {
                document.getElementById('error-data').textContent = JSON.stringify(details, null, 2);
            }
            
            // Message the parent window about the error
            if (window.opener) {
                window.opener.postMessage({
                    type: 'LAZADA_AUTH',
                    success: false,
                    error: message
                }, '*');
            }
            
            // Don't close automatically on error
        }
        
        // Start processing immediately
        document.addEventListener('DOMContentLoaded', processCode);
    </script>
</body>
</html> 