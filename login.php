<?php
session_start();

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi email harus naya@gmail.com dan password harus 12345678
    if (strtolower($email) !== 'fauzan@gmail.com') {
        $error_message = 'Email tidak terdaftar! Gunakan: fauzan@gmail.com';
    } elseif ($password !== '12345678') {
        $error_message = 'Password salah! Gunakan: 12345678';
    } else {
        // Simpan status login ke session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email']     = $email;

        // Redirect ke halaman dashboard
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ruang abu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background:
                linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)),
                url('https://png.pngtree.com/thumb_back/fh260/background/20210908/pngtree-mottled-wall-texture-abstract-light-gray-background-image_808475.jpg')
                center / cover no-repeat fixed;
            padding: 20px;
        }

        .glass-card {
            width: 100%;
            max-width: 360px;
            padding: 40px 30px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-title {
            color: #ffffff;
            font-size: 22px;
            margin-bottom: 24px;
            text-align: center;
        }

        /* Avatar Box di Tengah */
        .avatar-box {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.7);
        }

        .avatar-box svg {
            width: 44px;
            height: 44px;
            fill: currentColor;
        }

        /* Alert Pesan Error */
        .alert-error {
            width: 100%;
            font-size: 12px;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            background-color: rgba(231, 76, 60, 0.25);
            color: #ff8b8b;
            border: 1px solid rgba(231, 76, 60, 0.4);
        }

        form {
            width: 100%;
        }

        /* Group Input dengan Underline */
        .input-group {
            position: relative;
            margin-bottom: 22px;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .input-group svg {
            position: absolute;
            left: 4px;
            width: 16px;
            height: 16px;
            fill: rgba(255, 255, 255, 0.7);
        }

        .input-group input {
            width: 100%;
            padding: 10px 10px 10px 30px;
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
            outline: none;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }

        .input-group input:focus {
            border-bottom: 1px solid #ffffff;
        }

        /* Remember Me & Forgot Password */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 28px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .remember-me input {
            accent-color: #4667d4;
            cursor: pointer;
        }

        .forgot-pass {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-pass:hover {
            color: #ffffff;
        }

        /* Tombol Submit*/
        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(90deg, #0d0c0d 0%, #141414 50%, #101010 100%);
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.5px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <!-- Avatar Icon -->
        <div class="avatar-box">
            <svg viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert-error"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <h1 class="login-title">Login Ruang Abu</h1>

        <form method="POST" action="">
            <!-- Email ID -->
            <div class="input-group">
                <svg viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
                <input type="email" name="email" placeholder="Email ID" required value="">
            </div>

            <!-- Password -->
            <div class="input-group">
                <svg viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
                <input type="password" name="password" placeholder="Password" required value="">
            </div>

            <!-- Options Row -->
            <div class="options-row">
                <label class="remember-me">
                    <input type="checkbox" name="remember" checked> Remember me
                </label>
            
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">LOGIN</button>
        </form>
    </div>

</body>
</html>