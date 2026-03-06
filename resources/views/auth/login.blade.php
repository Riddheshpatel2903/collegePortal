<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EduPortal</title>
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
            opacity: 0.15;
            pointer-events: none;
        }

        .bg-orb-1 {
            width: 600px;
            height: 600px;
            background: #7c3aed;
            top: -200px;
            left: -200px;
            animation: float 20s ease-in-out infinite;
        }

        .bg-orb-2 {
            width: 500px;
            height: 500px;
            background: #a78bfa;
            bottom: -150px;
            right: -150px;
            animation: float 25s ease-in-out infinite reverse;
        }

        .bg-orb-3 {
            width: 300px;
            height: 300px;
            background: #c084fc;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: float 15s ease-in-out infinite;
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

        .login-card {
            width: 100%;
            max-width: 460px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
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
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 16px;
            border-radius: 16px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.4);
        }

        .brand h2 {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
        }

        .brand p {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .role-row {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .role-btn {
            flex: 1;
            padding: 14px;
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            text-align: center;
            background: #fff;
            transition: all 0.25s;
        }

        .role-btn i {
            display: block;
            font-size: 20px;
            color: #94a3b8;
            margin-bottom: 4px;
            transition: color 0.25s;
        }

        .role-btn span {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            transition: color 0.25s;
        }

        .role-btn.active {
            border-color: #7c3aed;
            background: rgba(124, 58, 237, 0.06);
        }

        .role-btn.active i,
        .role-btn.active span {
            color: #7c3aed;
        }

        .role-btn:hover:not(.active) {
            border-color: #c4b5fd;
        }

        .field {
            margin-bottom: 16px;
            position: relative;
        }

        .field i.icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            z-index: 2;
        }

        .field input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: all 0.25s;
        }

        .field input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
            background: #fff;
        }

        .row-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .row-between label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            cursor: pointer;
            font-weight: 500;
        }

        .row-between label input {
            width: 16px;
            height: 16px;
            accent-color: #7c3aed;
        }

        .row-between a {
            color: #7c3aed;
            font-weight: 600;
            text-decoration: none;
        }

        .row-between a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            box-shadow: 0 12px 32px rgba(124, 58, 237, 0.4);
            transform: translateY(-2px);
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #94a3b8;
        }

        .footer-link a {
            color: #7c3aed;
            font-weight: 700;
            text-decoration: none;
        }

        .error-msg {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }

        .copyright {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <div>
        <div class="login-card">
            <div class="brand">
                <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <h2>EduPortal</h2>
                <p>Online College Management System</p>
            </div>

            @if (session('status'))
                <div
                    style="padding: 12px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; color: #059669; font-size: 13px; font-weight: 600; margin-bottom: 20px;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <p
                    style="font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                    Sign in as</p>
                <div class="role-row">
                    <div class="role-btn active" onclick="selectRole('teacher')"><i
                            class="bi bi-person-badge"></i><span>Teacher</span></div>
                    <div class="role-btn" onclick="selectRole('student')"><i
                            class="bi bi-person-video3"></i><span>Student</span></div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="teacher">

                <div class="field">
                    <i class="bi bi-envelope icon"></i>
                    <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required
                        autofocus>
                </div>
                @error('email') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                @enderror

                <div class="field">
                    <i class="bi bi-shield-lock icon"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                @error('password') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                @enderror

                <div class="row-between">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">Log In <i class="bi bi-arrow-right"
                        style="margin-left: 8px;"></i></button>

                <div class="footer-link">
                    Don't have an account? <a href="{{ route('register') }}">Create Account</a>
                </div>
            </form>
        </div>
        <div class="copyright">&copy; 2026 EduPortal. All rights reserved.</div>
    </div>

    <script>
        function selectRole(role) {
            document.getElementById('roleInput').value = role;
            document.querySelectorAll('.role-btn').forEach(c => c.classList.remove('active'));
            Array.from(document.querySelectorAll('.role-btn')).find(c => c.innerText.toLowerCase().includes(role))?.classList.add('active');
        }
    </script>
</body>

</html>