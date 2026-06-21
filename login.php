<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    
    if (empty($identifier) || empty($password)) {
        $error = 'Email/nomor HP dan password harus diisi.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR no_hp = ? LIMIT 1");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                $redirect = $_GET['redirect'] ?? 'dashboard.php';
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Email/nomor HP atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – GARAGEZKA</title>
    <meta name="description" content="Masuk ke akun GARAGEZKA Anda untuk melanjutkan servis motor.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-text">GARAGE<span>ZKA</span></div>
        </div>
        <h1 class="auth-title">Selamat Datang Kembali</h1>
        <p class="auth-subtitle">Masuk untuk melanjutkan servis motor Anda</p>

        <?php if ($error): ?>
        <div class="alert alert-danger">❌ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>">
            <div class="form-group">
                <label class="form-label">Email atau nomor HP</label>
                <input type="text" id="identifier" name="identifier" class="form-control"
                       placeholder="nama@email.com atau 0812..."
                       value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                    Password
                    <a href="lupa-password.php" class="forgot-link">Lupa Password?</a>
                </label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Masukkan password Anda" required>
                    <button type="button" class="input-group-icon" id="togglePassword" onclick="togglePass()">👁️</button>
                </div>
            </div>
            <div class="remember-row">
                <label class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember">
                    Ingat Saya
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" id="btn-login">MASUK</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="daftar.php">Daftar</a>
        </div>
        <div style="text-align: center; margin-top: 28px; font-size: 0.72rem; color: var(--text-muted);">
            &copy; <?= date('Y') ?> GARAGEZKA. Presisi dalam Performa.
        </div>
    </div>

    <script>
    function togglePass() {
        const pw = document.getElementById('password');
        const btn = document.getElementById('togglePassword');
        if (pw.type === 'password') {
            pw.type = 'text';
            btn.textContent = '🙈';
        } else {
            pw.type = 'password';
            btn.textContent = '👁️';
        }
    }
    </script>
</body>
</html>
