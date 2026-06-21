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
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi_password'] ?? '';
    $agree    = $_POST['agree'] ?? '';

    if (empty($nama) || empty($email) || empty($no_hp) || empty($password)) {
        $error = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirm) {
        $error = 'Konfirmasi password tidak sesuai.';
    } elseif (!$agree) {
        $error = 'Anda harus menyetujui syarat dan ketentuan.';
    } else {
        $conn = getConnection();
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR no_hp = ?");
        $checkStmt->bind_param("ss", $email, $no_hp);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'Email atau nomor HP sudah terdaftar.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, email, no_hp, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $no_hp, $hashed);
            if ($stmt->execute()) {
                $success = 'Akun berhasil dibuat! Silakan masuk.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar â€“ GARAGEZKA</title>
    <meta name="description" content="Buat akun GARAGEZKA untuk booking servis motor tanpa antri.">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card" style="max-width: 480px;">
        <div class="auth-logo">
            <div class="auth-logo-text">GARAGE<span>ZKA</span></div>
        </div>
        <h1 class="auth-title">Buat Akun Baru</h1>
        <p class="auth-subtitle">Daftar untuk booking servis tanpa antri</p>

        <?php if ($error): ?>
        <div class="alert alert-danger">âŒ <?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success">âœ… <?= $success ?> <a href="login.php" style="color: inherit; font-weight: 700;">Masuk sekarang</a></div>
        <?php endif; ?>

        <form method="POST" action="daftar.php">
            <div class="form-group">
                <label class="form-label">Nama lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control"
                       placeholder="John Doe"
                       value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="contoh@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor HP</label>
                <input type="tel" name="no_hp" class="form-control"
                       placeholder="0812xxxx"
                       value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi password</label>
                    <input type="password" name="konfirmasi_password" class="form-control"
                           placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox-group">
                    <input type="checkbox" name="agree" required>
                    Saya menyetujui <a href="#" style="color: var(--accent);">Syarat &amp; Ketentuan</a> serta <a href="#" style="color: var(--accent);">Kebijakan Privasi</a> GARAGEZKA.
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">DAFTAR</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Masuk</a>
        </div>
        <div style="text-align: center; margin-top: 20px; font-size: 0.72rem; color: var(--text-muted);">
            &copy; <?= date('Y') ?> GARAGEZKA. Presisi dalam Performa.
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
