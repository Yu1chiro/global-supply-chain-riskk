{{-- Tampilan: reset password --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Supply Chain Risk Intelligence</title>
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
        html, body { margin: 0; height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Inter, Roboto, Arial, sans-serif;
            background: #f9fafb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(16, 19, 35, 0.08);
            padding: 44px 40px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
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
        .brand span { font-weight: 700; font-size: 18px; color: var(--ink); }
        h1 { font-size: 26px; font-weight: 700; color: var(--ink); margin: 0 0 8px; }
        p.sub { color: var(--muted); margin: 0 0 28px; font-size: 14.5px; line-height: 1.5; }
        .alert-error {
            background: #fef3f2;
            border: 1px solid #fda29b;
            color: #b42318;
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
        .field input[readonly] { background: #f2f4f7; color: var(--muted); }
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
        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--muted);
        }
        .footer-link a { color: var(--primary); font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand">
            <div class="mark"><i class="fas fa-route"></i></div>
            <span>RiskIntel</span>
        </div>

        <h1>Atur ulang password</h1>
        <p class="sub">Masukkan password baru untuk akun Anda.</p>

        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required>
            </div>

            <div class="field">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required minlength="8">
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" required minlength="8">
            </div>

            <button type="submit" class="btn-primary">Simpan Password Baru</button>
        </form>

        <p class="footer-link">
            <a href="{{ route('login') }}">Kembali ke Login</a>
        </p>
    </div>
</body>
</html>
