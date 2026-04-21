<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | College Management Portal</title>
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
            padding: 40px 20px;
            overflow-x: hidden;
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
            max-width: 500px;
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
            margin-bottom: 22px;
        }

        .role-btn {
            flex: 1;
            padding: 12px;
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
        }

        .role-btn span {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }

        .role-btn.active {
            border-color: #7c3aed;
            background: rgba(124, 58, 237, 0.06);
        }

        .role-btn.active i,
        .role-btn.active span {
            color: #7c3aed;
        }

        .field {
            margin-bottom: 14px;
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
            padding: 13px 16px 13px 48px;
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

        .btn-submit {
            width: 100%;
            padding: 15px;
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
            margin-top: 8px;
        }

        .btn-submit:hover {
            box-shadow: 0 12px 32px rgba(124, 58, 237, 0.4);
            transform: translateY(-2px);
        }

        .footer-link {
            text-align: center;
            margin-top: 18px;
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
            margin-top: 24px;
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

    <div>
        <div class="card">
            <div class="brand">
                <div class="brand-icon"><i class="bi bi-person-plus-fill"></i></div>
                <h2>Create Account</h2>
                <p>Join the College Management Portal Community</p>
            </div>

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <p
                    style="font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">
                    I am a</p>
                <div class="role-row">
                    <div class="role-btn active" onclick="selectRole('teacher')"><i
                            class="bi bi-person-badge"></i><span>Teacher</span></div>
                    <div class="role-btn" onclick="selectRole('student')"><i
                            class="bi bi-person-video3"></i><span>Student</span></div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="teacher">

                <div class="field"><i class="bi bi-person icon"></i><input type="text" name="name"
                        placeholder="Full Name" value="{{ old('name') }}" required autofocus></div>
                @error('name') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                @enderror

                <div class="field"><i class="bi bi-envelope icon"></i><input type="email" name="email"
                        placeholder="Email Address" value="{{ old('email') }}" required></div>
                @error('email') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                @enderror

                <div class="field"><i class="bi bi-shield-lock icon"></i><input type="password" name="password"
                        placeholder="Password" required></div>
                @error('password') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                @enderror

                <div class="field"><i class="bi bi-shield-check icon"></i><input type="password"
                        name="password_confirmation" placeholder="Confirm Password" required></div>

                <button type="submit" class="btn-submit">Create Account <i class="bi bi-check2-circle"
                        style="margin-left: 8px;"></i></button>

                <div class="footer-link">Already have an account? <a href="{{ route('login') }}">Log In</a></div>
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