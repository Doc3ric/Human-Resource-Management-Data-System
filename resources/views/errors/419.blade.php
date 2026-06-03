<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired — HDMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #10327c 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 52px 48px;
            max-width: 480px; width: 100%;
            text-align: center;
            box-shadow: 0 32px 80px rgba(0,0,0,.4);
        }
        .icon-wrap {
            width: 80px; height: 80px; border-radius: 20px;
            background: rgba(251,191,36,.15);
            border: 1px solid rgba(251,191,36,.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 28px;
            font-size: 36px;
        }
        .code {
            font-size: 13px; font-weight: 700; letter-spacing: 2px;
            color: rgba(251,191,36,.8); text-transform: uppercase;
            margin-bottom: 12px;
        }
        h1 {
            font-size: 28px; font-weight: 800; color: #fff;
            margin-bottom: 14px; line-height: 1.2;
        }
        p {
            font-size: 14px; color: rgba(255,255,255,.6);
            line-height: 1.7; margin-bottom: 32px;
        }
        .actions { display: flex; flex-direction: column; gap: 12px; }
        .btn-primary {
            display: block; padding: 14px 24px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff; font-size: 14px; font-weight: 700;
            border-radius: 12px; text-decoration: none;
            transition: all .2s;
            box-shadow: 0 4px 14px rgba(37,99,235,.4);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.5); }
        .btn-secondary {
            display: block; padding: 13px 24px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            color: rgba(255,255,255,.8); font-size: 14px; font-weight: 600;
            border-radius: 12px; text-decoration: none; transition: all .2s;
        }
        .btn-secondary:hover { background: rgba(255,255,255,.14); color: #fff; }
        .divider {
            border: none; border-top: 1px solid rgba(255,255,255,.1);
            margin: 28px 0;
        }
        .hint {
            font-size: 12px; color: rgba(255,255,255,.35);
            line-height: 1.6;
        }
        .hint strong { color: rgba(255,255,255,.5); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">⏱️</div>
        <div class="code">Error 419</div>
        <h1>Session Expired</h1>
        <p>
            Your page session has expired or the security token was no longer valid.
            This usually happens after being inactive for too long, or after a system update.
        </p>
        <div class="actions">
            <a href="/login" class="btn-primary">🔑 Go to Login Page</a>
            <a href="javascript:history.back()" class="btn-secondary">← Go Back & Try Again</a>
        </div>
        <hr class="divider">
        <p class="hint">
            <strong>Tip:</strong> If this keeps happening, try clearing your browser cookies
            for this site or opening a fresh browser tab.
        </p>
    </div>
</body>
</html>
