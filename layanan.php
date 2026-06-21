<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$conn = getConnection();
$layananList = $conn->query("SELECT * FROM layanan WHERE is_active = 1 ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

$iconMap = [
    'oil' => 'ðŸ›¢ï¸',
    'tune' => 'ðŸ”§',
    'service' => 'ðŸ”©',
    'electric' => 'âš¡',
    'tire' => 'ðŸ›ž',
    'brake' => 'ðŸ”´',
    'wash' => 'âœ¨',
    'overhaul' => 'âš™ï¸',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan â€“ GARAGEZKA</title>
    <meta name="description" content="GARAGEZKA menyediakan standar perawatan teknis untuk kendaraan Anda. Mulai dari ganti oli, tune up, servis lengkap hingga overhaul.">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php renderNavbar('layanan'); ?>

    <section class="section">
        <span class="section-label">LAYANAN PROFESIONAL</span>
        <h1 class="section-title">Presisi dalam Performa</h1>
        <p class="section-subtitle">Kami menyediakan standar perawatan teknis untuk kendaraan Anda. Setiap sentuhan dilakukan dengan ketelitian diagnostik.</p>

        <div class="layanan-page-grid">
            <?php foreach ($layananList as $layanan): ?>
            <div class="layanan-card">
                <div class="layanan-card-icon"><?= $iconMap[$layanan['icon']] ?? 'ðŸ”§' ?></div>
                <div class="layanan-card-name"><?= htmlspecialchars($layanan['nama_layanan']) ?></div>
                <div class="layanan-card-desc"><?= htmlspecialchars($layanan['deskripsi']) ?></div>
                <div class="layanan-card-price"><?= formatRupiah($layanan['harga']) ?></div>
                <a href="<?= isset($_SESSION['user_id']) ? 'booking.php?layanan_id=' . $layanan['id'] : 'login.php' ?>"
                   class="btn btn-primary btn-full">Pilih Layanan</a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php renderFooter(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
