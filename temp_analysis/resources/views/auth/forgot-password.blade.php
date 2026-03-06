<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | EduPortal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0a1a 0%, #1a1035 50%, #0f172a 100%);
            padding: 20px;
            overflow: hidden;
        }

        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.12;
            pointer-events: none;
        }

        .bg-orb-1 {
            width: 500px;
            height: 500px;
            background: #f59e0b;
            top: -200px;
            left: -200px;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(30px, -30px);
            }
        }

        .card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 44px 40px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 16px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
        }

        .brand h2 {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
        }

        .brand p {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .field input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: all 0.25s;
        }

        .field input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
            background: #fff;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            margin-top: 8px;
        }

        .btn-submit:hover {
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.4);
            transform: translateY(-2px);
        }

        .back-link {
            text-align: center;
            margin-top: 18px;
        }

        .back-link a {
            color: #7c3aed;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .error-msg {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }

        .success-msg {
            padding: 12px 16px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            color: #059669;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <div class="bg-orb bg-orb-1"></div>
    <div class="card">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-key-fill"></i></div>
            <h2>Forgot Password</h2>
            <p>Enter your email and we'll send you a link to reset your password.</p>
        </div>

        <x-auth-session-status class="success-msg" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@college.edu" value="{{ old('email') }}" required
                    autofocus>
            </div>
            @error('email') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p> @enderror

            <button type="submit" class="btn-submit">Send Reset Link <i class="bi bi-send-fill"
                    style="margin-left: 8px;"></i></button>

            <div class="back-link"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
        </form>
    </div>
</body>

</html>