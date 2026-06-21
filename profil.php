<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn   = getConnection();
$userId = $_SESSION['user_id'];
$error  = '';
$success = '';

$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $nama  = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');

        if (empty($nama) || empty($email) || empty($no_hp)) {
            $error = 'Semua field harus diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            // Check unique
            $check = $conn->prepare("SELECT id FROM users WHERE (email = ? OR no_hp = ?) AND id != ?");
            $check->bind_param("ssi", $email, $no_hp, $userId);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = 'Email atau nomor HP sudah digunakan akun lain.';
            } else {
                $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nama, $email, $no_hp, $userId);
                if ($stmt->execute()) {
                    $_SESSION['user_name'] = $nama;
                    $success = 'Profil berhasil diperbarui.';
                    $user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
                } else {
                    $error = 'Gagal memperbarui profil.';
                }
            }
        }
    } elseif ($action === 'update_password') {
        $oldPw  = $_POST['password_lama'] ?? '';
        $newPw  = $_POST['password_baru'] ?? '';
        $konfPw = $_POST['konfirmasi_password'] ?? '';

        if (!password_verify($oldPw, $user['password'])) {
            $error = 'Password lama salah.';
        } elseif (strlen($newPw) < 6) {
            $error = 'Password baru minimal 6 karakter.';
        } elseif ($newPw !== $konfPw) {
            $error = 'Konfirmasi password tidak sesuai.';
        } else {
            $hashed = password_hash($newPw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $userId);
            if ($stmt->execute()) {
                $success = 'Password berhasil diperbarui.';
            } else {
                $error = 'Gagal mengubah password.';
            }
        }
    }
}

$initial = strtoupper(substr($user['nama_lengkap'], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil â€“ GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('profil'); ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Profil</h1>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">âŒ <?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success">âœ… <?= $success ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 900px;">
            <!-- INFO PROFIL -->
            <div class="card">
                <div class="profile-avatar-lg">
                    <?= $initial ?>
                </div>
                <h2 style="font-size: 1rem; font-weight: 700; margin-bottom: 20px; text-align: center;">Informasi Akun</h2>
                
                <form method="POST" action="profil.php">
                    <input type="hidden" name="action" value="update_profil">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control"
                               value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor HP</label>
                        <input type="tel" name="no_hp" class="form-control"
                               value="<?= htmlspecialchars($user['no_hp']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Member Sejak</label>
                        <input type="text" class="form-control" value="<?= date('d F Y', strtotime($user['created_at'])) ?>" disabled style="opacity: 0.5;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">Simpan Perubahan</button>
                </form>
            </div>

            <!-- UBAH PASSWORD -->
            <div class="card">
                <h2 style="font-size: 1rem; font-weight: 700; margin-bottom: 20px;">Ubah Password</h2>
                <form method="POST" action="profil.php">
                    <input type="hidden" name="action" value="update_password">
                    <div class="form-group">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control"
                               placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control"
                               placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" class="form-control"
                               placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">Update Password</button>
                </form>

                <!-- LOGOUT -->
                <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <a href="logout.php" class="btn btn-outline btn-full" style="color: var(--accent); border-color: rgba(229,53,53,0.3);">
                        Keluar dari Akun
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
