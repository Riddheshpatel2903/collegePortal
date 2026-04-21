<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | College Management Portal</title>
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
            background: #f1f5f9;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            position: relative;
            z-index: 1;
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            border-radius: 12px;
            background: #6366f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
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
        }

        .role-row {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .role-btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            text-align: center;
            background: #fff;
            transition: all 0.2s;
        }

        .role-btn i {
            display: block;
            font-size: 18px;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .role-btn span {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        .role-btn.active {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .role-btn.active i,
        .role-btn.active span {
            color: #6366f1;
        }

        .field {
            margin-bottom: 16px;
            position: relative;
        }

        .field i.icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }

        .field input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #1e293b;
            background: #ffffff;
            outline: none;
            transition: all 0.2s;
        }

        .field input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .row-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .row-between label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            cursor: pointer;
        }

        .row-between label input {
            width: 15px;
            height: 15px;
            accent-color: #6366f1;
        }

        .row-between a {
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #6366f1;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: #4f46e5;
        }

        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #64748b;
        }

        .footer-link a {
            color: #6366f1;
            font-weight: 700;
            text-decoration: none;
        }

        .error-msg {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }

        .copyright {
            text-align: center;
            margin-top: 32px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div>
        <div class="login-card">
            <div class="brand">
                <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <h2>College Management Portal</h2>
                <p> </p>
            </div>

            @if (session('status'))
                <div
                    style="padding: 12px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; color: #059669; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <p
                    style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
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
                @error('email') <p class="error-msg">{{ $message }}</p>
                @enderror

                <div class="field">
                    <i class="bi bi-shield-lock icon"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                @error('password') <p class="error-msg">{{ $message }}</p>
                @enderror

                <div class="row-between">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">Log In</button>

                <div class="footer-link">
                    Don't have an account? <a href="{{ route('register') }}">Create Account</a>
                </div>
            </form>
        </div>
        <div class="copyright">&copy; 2026 College Management Portal. All rights reserved.</div>
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