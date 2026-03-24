<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Offline — PSAU Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: #111827; }
        .card { background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; max-width: 400px; }
        .icon { font-size: 48px; margin-bottom: 16px; display: inline-block; background: #fee2e2; padding: 16px; border-radius: 50%; }
        h1 { font-size: 20px; font-weight: 800; margin: 0 0 10px; color: #991b1b; }
        p { font-size: 14px; color: #4b5563; line-height: 1.5; margin: 0 0 24px; }
        .btn { display: inline-block; padding: 10px 20px; background: #6b0a16; color: #fff; text-decoration: none; font-weight: 600; border-radius: 8px; font-size: 14px; transition: background 0.2s; }
        .btn:hover { background: #4e0710; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔌</div>
        <h1>System Offline</h1>
        <p>The database connection could not be established. Please ensure XAMPP (MySQL) is running and try again.</p>
        <a href="" class="btn">Reload Page</a>
    </div>
</body>
</html>
