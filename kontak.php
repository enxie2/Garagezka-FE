<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    
    if (empty($nama) || empty($email) || empty($pesan)) {
        $error = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO pesan_kontak (nama_lengkap, email, pesan) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $email, $pesan);
        if ($stmt->execute()) {
            $success = 'Pesan Anda telah berhasil dikirim. Kami akan menghubungi Anda segera.';
        } else {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak – GARAGEZKA</title>
    <meta name="description" content="Hubungi GARAGEZKA untuk informasi layanan servis motor atau booking. Kami siap membantu performa motor Anda.">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php renderNavbar('kontak'); ?>

    <section class="section">
        <h1 class="section-title">Hubungi Kami</h1>
        <p class="section-subtitle">Kami siap membantu performa motor Anda tetap presisi. Silakan hubungi tim dukungan kami melalui formulir atau informasi kontak di bawah.</p>

        <div class="kontak-grid" style="margin-top: 40px;">
            <div class="kontak-info">
                <div class="kontak-item">
                    <div class="kontak-item-icon">📞</div>
                    <div>
                        <div class="kontak-item-label">Telepon &amp; WhatsApp</div>
                        <div class="kontak-item-value">+62 812 3456 7890</div>
                    </div>
                </div>
                <div class="kontak-item">
                    <div class="kontak-item-icon">✉️</div>
                    <div>
                        <div class="kontak-item-label">Email</div>
                        <div class="kontak-item-value">halo@garagezka.com</div>
                    </div>
                </div>
                <div class="kontak-item">
                    <div class="kontak-item-icon">🕐</div>
                    <div>
                        <div class="kontak-item-label">Jam Operasional</div>
                        <div class="kontak-item-value">Senin – Sabtu<br>08:00 – 17:00 WIB</div>
                    </div>
                </div>
            </div>

            <div>
                <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger">âŒ <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="kontak.php">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama Anda" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pesan</label>
                        <textarea name="pesan" class="form-control" rows="5" placeholder="Ceritakan kebutuhan atau kendala motor Anda..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </section>

    <?php renderFooter(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
