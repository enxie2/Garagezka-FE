<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn   = getConnection();
$userId = $_SESSION['user_id'];

$riwayat = $conn->query("
    SELECT b.*, k.nama_kendaraan, k.nomor_plat, l.nama_layanan
    FROM booking b
    JOIN kendaraan k ON b.kendaraan_id = k.id
    JOIN layanan l ON b.layanan_id = l.id
    WHERE b.user_id = $userId
    ORDER BY b.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Servis – GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('riwayat'); ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Riwayat Servis</h1>
            <p class="page-subtitle">Lihat semua riwayat servis motor Anda.</p>
        </div>

        <div class="table-wrapper">
            <?php if (empty($riwayat)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔧</div>
                <h3>Belum ada riwayat servis</h3>
                <p>Mulai booking servis pertama Anda</p>
                <a href="booking.php" class="btn btn-primary" style="margin-top: 16px;">Booking Sekarang</a>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Kendaraan</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $i => $b): ?>
                    <tr>
                        <td style="color: var(--text-muted);"><?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('d M Y', strtotime($b['tanggal'])) ?></td>
                        <td>
                            <div style="font-weight: 500;"><?= htmlspecialchars($b['nama_kendaraan']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($b['nomor_plat']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($b['nama_layanan']) ?></td>
                        <td><?= getStatusBadge($b['status']) ?></td>
                        <td style="font-weight: 600;"><?= formatRupiah($b['total_biaya']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
