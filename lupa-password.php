<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan alamat email yang valid.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            // In production, send reset email here
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
            $upd->bind_param("sss", $token, $expires, $email);
            $upd->execute();
            $success = 'Link reset password telah dikirim ke email Anda (dalam demo: token = ' . $token . ')';
        } else {
            $success = 'Jika email terdaftar, link reset akan dikirimkan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password – GARAGEZKA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-text">GARAGE<span>ZKA</span></div>
        </div>
        <h1 class="auth-title">Lupa Password?</h1>
        <p class="auth-subtitle">Masukkan email Anda dan kami akan mengirimkan link untuk mereset password</p>

        <?php if ($error): ?>
        <div class="alert alert-danger">❌ <?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="lupa-password.php">
            <div class="form-group">
                <label class="form-label">Alamat Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="nama@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Kirim Link Reset</button>
        </form>

        <div class="auth-footer" style="margin-top: 20px;">
            <a href="login.php" style="color: var(--text-secondary);">← Kembali ke Login</a>
        </div>
        <div style="text-align: center; margin-top: 20px; font-size: 0.72rem; color: var(--text-muted);">
            &copy; <?= date('Y') ?> GARAGEZKA. Presisi dalam Performa.
        </div>
    </div>
</body>
</html>
