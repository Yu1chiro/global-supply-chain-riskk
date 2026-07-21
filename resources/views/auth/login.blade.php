{{-- Tampilan: login --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Supply Chain Risk Intelligence</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4338ca;
            --primary-dark: #3730a3;
            --ink: #101323;
            --muted: #667085;
            --border: #d0d5dd;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            height: 100%;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Inter, Roboto, Arial, sans-serif;
            background: #fff;
            min-height: 100vh;
            display: flex;
        }
        .auth-shell {
            width: 100%;
            min-height: 100vh;
            background: #fff;
            display: flex;
            overflow: hidden;
        }
        .auth-panel {
            flex: 1 1 50%;
            padding: 56px 64px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
        }
        .brand .mark {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: radial-gradient(circle at 30% 30%, #6366f1, #312e81);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 16px;
        }
        .brand span {
            font-weight: 700;
            font-size: 18px;
            color: var(--ink);
        }
        .auth-panel h1 {
            font-size: 34px;
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 10px;
        }
        .auth-panel p.sub {
            color: var(--muted);
            margin: 0 0 32px;
            font-size: 15px;
        }
        .alert-error {
            background: #fef3f2;
            border: 1px solid #fda29b;
            color: #b42318;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #ecfdf3;
            border: 1px solid #6ce9a6;
            color: #027a48;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 999;
            opacity: 0;
            transform: translateY(-10px);
            animation: toast-in 0.3s ease forwards, toast-out 0.3s ease forwards 3.7s;
        }
        @keyframes toast-in {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes toast-out {
            to { opacity: 0; transform: translateY(-10px); }
        }
        .alert-success {
            background: #ecfdf3;
            border: 1px solid #6ce9a6;
            color: #027a48;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }
        .field { margin-bottom: 20px; }
        .field label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: var(--ink);
            margin-bottom: 6px;
        }
        .field input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            color: var(--ink);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 56, 202, 0.12);
        }
        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--ink);
        }
        .row-between a {
            font-size: 14px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-primary:hover { background: var(--primary-dark); }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 22px 0;
            color: var(--muted);
            font-size: 13px;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--border);
        }
        .divider span { padding: 0 12px; }
        .btn-google {
            width: 100%;
            padding: 11px;
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: background .2s, border-color .2s;
        }
        .btn-google:hover { background: #f9fafb; border-color: #98a2b3; }
        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--muted);
        }
        .footer-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .copyright {
            font-size: 13px;
            color: var(--muted);
            margin-top: 48px;
        }
        .auth-visual {
            flex: 1 1 50%;
            background: linear-gradient(160deg, #4338ca 0%, #312e81 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            overflow: hidden;
        }
        .auth-visual::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(115deg, rgba(255,255,255,0.06) 0 2px, transparent 2px 26px);
        }
        .visual-card {
            position: relative;
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            width: 340px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            z-index: 2;
        }
        .visual-card h4 {
            margin: 0 0 12px;
            font-size: 14px;
            color: var(--ink);
        }
        .visual-card svg { display: block; width: 100%; height: 90px; }
        .visual-badge {
            position: relative;
            margin-top: -30px;
            margin-left: 200px;
            background: #fff;
            border-radius: 14px;
            padding: 16px 22px;
            width: 150px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            z-index: 3;
        }
        .visual-badge .ring {
            width: 74px;
            height: 74px;
            margin: 0 auto 8px;
            border-radius: 50%;
            background: conic-gradient(#4338ca 0 70%, #c7d2fe 70% 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .visual-badge .ring span {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: var(--ink);
        }
        .visual-badge small {
            color: var(--muted);
            font-size: 11px;
        }
        .visual-text {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
            margin-top: 40px;
        }
        .visual-text h3 { font-size: 22px; margin: 0 0 8px; }
        .visual-text p { font-size: 14px; opacity: 0.85; margin: 0; }
        @media (max-width: 900px) {
            .auth-visual { display: none; }
            .auth-panel { padding: 40px 28px; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-panel">
            <div class="brand">
                <div class="mark"><i class="fas fa-route"></i></div>
                <span>RiskIntel</span>
            </div>

            <h1>Log in</h1>
            <p class="sub">Silahkan masukkan email dan password Anda.</p>

            @if ($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email Anda"
                           value="{{ old('email') }}" required autofocus>
                </div>

                <div class="field">
                    <div class="row-between" style="margin-bottom:6px;">
                        <label for="password" style="margin-bottom:0;">Password</label>
                        <a href="{{ route('password.request') }}" style="font-size:13px;color:var(--primary);text-decoration:none;font-weight:600;">Lupa password?</a>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
                </div>

                <button type="submit" class="btn-primary">Sign in</button>
            </form>

            <div class="divider"><span>atau</span></div>

            <a href="{{ route('auth.google') }}" class="btn-google">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.83.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/>
                    <path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/>
                    <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/>
                </svg>
                <span>Lanjutkan dengan Google</span>
            </a>

            <p class="footer-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar dengan Email</a>
            </p>
        </div>

        <div class="auth-visual">
            <div class="visual-card">
                <h4>Risk overview</h4>
                <svg viewBox="0 0 300 90" xmlns="http://www.w3.org/2000/svg">
                    <polyline fill="none" stroke="#4338ca" stroke-width="2.5"
                        points="0,60 30,55 60,65 90,40 120,50 150,30 180,42 210,20 240,28 270,15 300,22" />
                    <polyline fill="none" stroke="#a5b4fc" stroke-width="2"
                        points="0,75 30,72 60,78 90,68 120,74 150,66 180,70 210,60 240,65 270,58 300,60" />
                </svg>
            </div>
            <div class="visual-badge">
                <div class="ring"><span>82%</span></div>
                <small>Skor risiko rata-rata</small>
            </div>
            <div class="visual-text">
                <h3>Global Supply Chain Risk Intelligence</h3>
                <p>Monitor supply chain risks, economic indicators, weather conditions, and logistics data from around the world.</p>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="toast" id="statusToast">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
        <script>
            setTimeout(() => {
                const t = document.getElementById('statusToast');
                if (t) t.remove();
            }, 4000);
        </script>
    @endif
</body>
</html>
