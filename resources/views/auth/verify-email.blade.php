<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | EduPortal</title>
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
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.12;
            pointer-events: none;
            background: #6366f1;
            bottom: -200px;
            left: -200px;
        }

        .card {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 44px 40px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
            text-align: center;
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

        .brand-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 20px;
            border-radius: 16px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        h2 {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .success-msg {
            padding: 12px 16px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            color: #059669;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            margin-bottom: 12px;
        }

        .btn-primary:hover {
            box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }

        .btn-secondary {
            width: 100%;
            padding: 15px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            background: white;
            color: #64748b;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }
    </style>
</head>

<body>
    <div class="bg-orb"></div>
    <div class="card">
        <div class="brand-icon"><i class="bi bi-envelope-check"></i></div>
        <h2>Verify Your Email</h2>
        <p class="desc">Thanks for signing up! Please verify your email address by clicking the link we sent to your
            inbox.</p>

        @if (session('status') == 'verification-link-sent')
            <div class="success-msg">A new verification link has been sent to your email address.</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary">Resend Verification Email <i class="bi bi-send-fill"
                    style="margin-left: 8px;"></i></button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary">Log Out <i class="bi bi-box-arrow-right"
                    style="margin-left: 8px;"></i></button>
        </form>
    </div>
</body>

</html>