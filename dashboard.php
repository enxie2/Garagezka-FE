<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn = getConnection();
$userId = $_SESSION['user_id'];

// Stats
$totalKendaraan = $conn->query("SELECT COUNT(*) as c FROM kendaraan WHERE user_id = $userId")->fetch_assoc()['c'];
$totalBooking   = $conn->query("SELECT COUNT(*) as c FROM booking WHERE user_id = $userId")->fetch_assoc()['c'];
$bookingSelesai = $conn->query("SELECT COUNT(*) as c FROM booking WHERE user_id = $userId AND status = 'selesai'")->fetch_assoc()['c'];
$totalNotifUnread = $conn->query("SELECT COUNT(*) as c FROM notifikasi WHERE user_id = $userId AND is_read = 0")->fetch_assoc()['c'];

// Latest booking
$latestBooking = $conn->query("
    SELECT b.*, k.nama_kendaraan, k.nomor_plat, l.nama_layanan, l.harga
    FROM booking b
    JOIN kendaraan k ON b.kendaraan_id = k.id
    JOIN layanan l ON b.layanan_id = l.id
    WHERE b.user_id = $userId
    ORDER BY b.created_at DESC LIMIT 1
")->fetch_assoc();

// Recent bookings
$recentBookings = $conn->query("
    SELECT b.*, k.nama_kendaraan, l.nama_layanan
    FROM booking b
    JOIN kendaraan k ON b.kendaraan_id = k.id
    JOIN layanan l ON b.layanan_id = l.id
    WHERE b.user_id = $userId
    ORDER BY b.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Recent notifikasi
$notifs = $conn->query("SELECT * FROM notifikasi WHERE user_id = $userId ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard â€“ GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('dashboard'); ?>

    <main class="main-content">
        <div class="page-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Kelola dan pantau semua aktivitas kendaraan Anda dalam satu dashboard.</p>
            </div>
            <a href="booking.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                Booking Servis Sekarang
            </a>
        </div>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Kendaraan</div>
                <div class="stat-value"><?= str_pad($totalKendaraan, 2, '0', STR_PAD_LEFT) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Booking</div>
                <div class="stat-value"><?= str_pad($totalBooking, 2, '0', STR_PAD_LEFT) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Servis Selesai</div>
                <div class="stat-value"><?= str_pad($bookingSelesai, 2, '0', STR_PAD_LEFT) ?></div>
            </div>
            <?php if ($latestBooking): ?>
            <div class="stat-card">
                <div class="stat-label">Estimasi Biaya Terakhir</div>
                <div class="stat-value" style="font-size: 1.3rem; color: var(--accent);"><?= formatRupiah($latestBooking['total_biaya']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($notifs): ?>
        <!-- NOTIFIKASI MINI -->
        <div class="table-wrapper" style="margin-bottom: 24px;">
            <div class="table-header">
                <span class="table-title">
                    NOTIFIKASI
                    <?php if ($totalNotifUnread > 0): ?>
                    <span class="badge badge-new" style="margin-left: 8px;"><?= $totalNotifUnread ?> BARU</span>
                    <?php endif; ?>
                </span>
                <a href="notifikasi.php" class="btn btn-ghost btn-sm">Lihat Semua â†’</a>
            </div>
            <?php foreach ($notifs as $n): ?>
            <div class="notif-item">
                <div class="notif-icon <?= $n['tipe'] ?>">
                    <?= $n['tipe'] === 'berhasil' ? 'ðŸ“…' : 'ðŸ”§' ?>
                </div>
                <div class="notif-content">
                    <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
                    <div class="notif-msg"><?= htmlspecialchars($n['pesan']) ?></div>
                </div>
                <div class="notif-time"><?= date('H:i', strtotime($n['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- RIWAYAT BOOKING -->
        <div class="table-wrapper">
            <div class="table-header">
                <span class="table-title">Riwayat Booking</span>
                <a href="riwayat.php" class="btn btn-ghost btn-sm">Lihat Semua â†’</a>
            </div>
            <?php if (empty($recentBookings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">ðŸ“‹</div>
                <h3>Belum ada booking</h3>
                <p>Mulai booking servis pertama Anda</p>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kendaraan</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($b['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($b['nama_kendaraan']) ?></td>
                        <td><?= htmlspecialchars($b['nama_layanan']) ?></td>
                        <td><?= getStatusBadge($b['status']) ?></td>
                        <td><?= formatRupiah($b['total_biaya']) ?></td>
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
