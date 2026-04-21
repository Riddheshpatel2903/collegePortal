<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | College Management Portal</title>
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
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.12;
            pointer-events: none;
            background: #10b981;
            top: -200px;
            right: -200px;
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
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
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
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            background: #fff;
        }

        .field input[readonly] {
            background: #f1f5f9;
            color: #64748b;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #10b981, #059669);
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
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }

        .error-msg {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="bg-orb"></div>
    <div class="card">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
            <h2>Reset Password</h2>
            <p>Create a new secure password for your account.</p>
        </div>
        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required readonly>
            </div>
            @error('email') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p> @enderror

            <div class="field">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Enter new password" required
                    autocomplete="new-password">
            </div>
            @error('password') <p class="error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
            @enderror

            <div class="field">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="Confirm new password" required
                    autocomplete="new-password">
            </div>

            <button type="submit" class="btn-submit">Reset Password <i class="bi bi-check2-circle"
                    style="margin-left: 8px;"></i></button>
        </form>
    </div>
</body>

</html>