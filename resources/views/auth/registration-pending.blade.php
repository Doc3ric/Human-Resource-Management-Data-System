<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted — HRMDS | Provincial Government of Bukidnon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        :root {
            --color-primary:      #113659;
            --color-primary-dark: #0d2842;
            --color-accent:       #2563eb;
        }

        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 1rem; background: #071f38; position: relative; overflow: hidden;
        }

        .bg-animated { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-animated::before {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 40%, #0d3057 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 70%, #0a2540 0%, transparent 60%),
                radial-gradient(ellipse 100% 80% at 50% 0%, #071f38 0%, #030f1e 100%);
        }

        .card {
            position: relative; z-index: 10; width: 100%; max-width: 460px;
            background: #fff; border-radius: 20px; padding: 2.5rem 2.25rem; text-align: center;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.07), 0 30px 80px rgba(0,0,0,0.55), 0 8px 20px rgba(0,0,0,0.3);
        }

        .icon-circle {
            width: 68px; height: 68px; border-radius: 50%; margin: 0 auto 1.25rem;
            background: #eef6ff; border: 1px solid #c3e0fb;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #2176c7;
        }

        h1 { font-size: 1.4rem; font-weight: 900; color: #0a1525; margin: 0 0 0.6rem; letter-spacing: -0.02em; }
        p.lead { font-size: 13px; color: #4a5568; font-weight: 500; line-height: 1.6; margin: 0 0 1.5rem; }

        .status-box {
            background: #f7f9fc; border: 1px solid #e8edf4; border-radius: 10px;
            padding: 0.85rem 1rem; margin-bottom: 1.5rem; text-align: left;
            display: flex; gap: 0.6rem; align-items: flex-start;
        }
        .status-box i { color: #d69e2e; font-size: 15px; margin-top: 1px; flex-shrink: 0; }
        .status-box p { font-size: 11.5px; color: #4a5568; font-weight: 500; margin: 0; line-height: 1.5; }
        .status-box strong { color: #0a1525; }

        .btn-back {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            width: 100%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: #fff; font-size: 13px; font-weight: 800; letter-spacing: 0.05em;
            padding: 0.78rem 1rem; border-radius: 10px; text-decoration: none;
            box-shadow: 0 4px 14px rgba(8,32,64,0.35); transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-back:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(8,32,64,0.45); }

        .footer-note { margin-top: 1.25rem; font-size: 9.5px; color: #b0bec5; letter-spacing: 0.05em; }
    </style>
</head>

<body>
    <div class="bg-animated"></div>

    <div class="card">
        <div class="icon-circle"><i class="bi bi-hourglass-split"></i></div>
        <h1>Request Submitted</h1>
        <p class="lead">
            Thank you, {{ session('registered_name', 'and welcome') }}. Your account request has been received.
        </p>

        <div class="status-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <p>
                <strong>Pending Super Administrator approval.</strong>
                You will not be able to sign in until your request has been reviewed and approved.
                This is verified manually to protect access to Personnel Records.
            </p>
        </div>

        <a href="{{ route('login') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
            Back to Sign In
        </a>

        <div class="footer-note">© {{ date('Y') }} Provincial Government of Bukidnon — PHRMO</div>
    </div>
</body>
</html>
