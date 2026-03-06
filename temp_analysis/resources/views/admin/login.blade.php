<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — EduPortal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0a1a 0%, #1a1035 40%, #0c1222 100%);
            overflow: hidden;
            position: relative;
        }

        /* Animated background orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(124,58,237,0.25), transparent 70%);
            top: -10%; left: -5%;
            animation: float1 18s ease-in-out infinite;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(168,85,247,0.2), transparent 70%);
            bottom: -15%; right: -5%;
            animation: float2 22s ease-in-out infinite;
        }
        .orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(99,102,241,0.15), transparent 70%);
            top: 50%; left: 60%;
            animation: float3 15s ease-in-out infinite;
        }

        @keyframes float1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(40px,-30px) scale(1.1); } }
        @keyframes float2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,40px) scale(1.05); } }
        @keyframes float3 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-20px,-20px); } }

        /* Glass card */
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            background: rgba(255,255,255,0.97);
            border-radius: 1.75rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 30px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo */
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            box-shadow: 0 8px 30px rgba(124,58,237,0.4);
        }
        .logo-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.02em;
        }
        .logo-subtitle {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-top: 0.25rem;
        }

        /* Admin badge */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(168,85,247,0.1));
            border: 1px solid rgba(124,58,237,0.15);
            border-radius: 999px;
            font-size: 0.625rem;
            font-weight: 700;
            color: #7c3aed;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.5rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.875rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: all 0.25s ease;
        }
        .form-input:focus {
            border-color: #7c3aed;
            background: white;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.08);
        }
        .form-input::placeholder {
            color: #cbd5e1;
            font-weight: 400;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            transition: color 0.25s;
        }
        .input-wrapper:focus-within .input-icon {
            color: #7c3aed;
        }

        /* Remember me */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            width: 1rem; height: 1rem;
            accent-color: #7c3aed;
            border-radius: 0.25rem;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            font-size: 0.875rem;
            font-weight: 700;
            border: none;
            border-radius: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.02em;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            box-shadow: 0 12px 35px rgba(124,58,237,0.4);
            transform: translateY(-2px);
        }

        /* Error */
        .error-box {
            padding: 0.75rem 1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .error-text {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #dc2626;
        }

        /* Footer */
        .card-footer {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }
        .card-footer a {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #7c3aed;
            text-decoration: none;
            transition: color 0.2s;
        }
        .card-footer a:hover {
            color: #5b21b6;
        }
        .card-footer span {
            color: #94a3b8;
            font-size: 0.8125rem;
        }

        /* Grid background pattern */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }
    </style>
</head>

<body>
    <div class="grid-pattern"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 class="logo-title">EduPortal</h1>
            <p class="logo-subtitle">Management System</p>
        </div>

        <div style="text-align: center;">
            <span class="admin-badge">
                <i class="bi bi-gear-fill"></i> Administrator Access
            </span>
        </div>

        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    <p class="error-text"><i class="bi bi-exclamation-circle-fill" style="margin-right: 6px;"></i>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrapper">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-input" placeholder="admin@eduportal.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>

            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right" style="margin-right: 6px;"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="card-footer">
            <span>Not an admin?</span> <a href="{{ route('login') }}">Go to Portal Login</a>
        </div>
    </div>
</body>

</html>
