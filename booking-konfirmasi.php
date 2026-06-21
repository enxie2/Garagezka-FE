<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn   = getConnection();
$userId = $_SESSION['user_id'];
$bookingId = (int)($_GET['id'] ?? 0);

if (!$bookingId) {
    header('Location: dashboard.php');
    exit;
}

$booking = $conn->query("
    SELECT b.*, k.nama_kendaraan, k.nomor_plat, l.nama_layanan, l.harga, l.estimasi_waktu
    FROM booking b
    JOIN kendaraan k ON b.kendaraan_id = k.id
    JOIN layanan l ON b.layanan_id = l.id
    WHERE b.id = $bookingId AND b.user_id = $userId
")->fetch_assoc();

if (!$booking) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Terkonfirmasi â€“ GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('booking'); ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Booking Servis</h1>
        </div>

        <div style="max-width: 640px;">
            <!-- SUCCESS BANNER -->
            <div style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); border-radius: 12px; padding: 24px; display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <div style="font-size: 2rem;">âœ…</div>
                <div>
                    <div style="font-size: 1rem; font-weight: 700; color: var(--success); margin-bottom: 4px;">Booking Dikonfirmasi!</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Nomor Booking: <strong>#<?= str_pad($bookingId, 5, '0', STR_PAD_LEFT) ?></strong></div>
                </div>
            </div>

            <!-- BOOKING DETAIL -->
            <div class="card">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px;">Detail Booking</h2>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Kendaraan</span>
                        <span style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($booking['nama_kendaraan']) ?> (<?= htmlspecialchars($booking['nomor_plat']) ?>)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Layanan</span>
                        <span style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($booking['nama_layanan']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Jadwal</span>
                        <span style="font-weight: 600; font-size: 0.875rem;"><?= date('d M Y', strtotime($booking['tanggal'])) ?>, <?= date('H:i', strtotime($booking['jam'])) ?> WIB</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Estimasi Waktu</span>
                        <span style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($booking['estimasi_waktu']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Biaya Jasa Servis</span>
                        <span style="font-weight: 600; font-size: 0.875rem;"><?= formatRupiah($booking['biaya_servis']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Biaya Admin</span>
                        <span style="font-weight: 600; font-size: 0.875rem;"><?= formatRupiah($booking['biaya_admin']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700;">Total Biaya</span>
                        <span style="font-weight: 800; font-size: 1.2rem; color: var(--accent);"><?= formatRupiah($booking['total_biaya']) ?></span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <a href="dashboard.php" class="btn btn-outline">Ke Dashboard</a>
                <a href="riwayat.php" class="btn btn-primary">Lihat Riwayat Servis</a>
            </div>
        </div>
    </main>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
