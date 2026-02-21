<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 - Unauthorized</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { text-align: center; padding: 2rem; }
        .error-code { font-size: 8rem; font-weight: bold; color: #ef4444; }
        .error-title { font-size: 2rem; margin-bottom: 1rem; }
        .error-message { color: #94a3b8; margin-bottom: 2rem; }
        .btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
        }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">401</div>
        <h1 class="error-title">Unauthorized</h1>
        <p class="error-message">You need to be logged in to access this page.</p>
        <a href="/login" class="btn">Login</a>
    </div>
</body>
</html>