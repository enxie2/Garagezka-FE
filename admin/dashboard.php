<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
requireAdmin();

$conn = getConnection();

// Stats global
$totalUsers    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalBooking  = $conn->query("SELECT COUNT(*) as c FROM booking")->fetch_assoc()['c'];
$bookingPending= $conn->query("SELECT COUNT(*) as c FROM booking WHERE status='pending'")->fetch_assoc()['c'];
$totalRevenue  = $conn->query("SELECT COALESCE(SUM(total_biaya),0) as t FROM booking WHERE status='selesai'")->fetch_assoc()['t'];
$pesanBaru     = $conn->query("SELECT COUNT(*) as c FROM pesan_kontak WHERE is_read=0")->fetch_assoc()['c'];
$bookingHariIni= $conn->query("SELECT COUNT(*) as c FROM booking WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];

// Booking terbaru (semua user)
$recentBookings = $conn->query("
    SELECT b.*, u.nama_lengkap, k.nama_kendaraan, k.nomor_plat, l.nama_layanan
    FROM booking b
    JOIN users u ON b.user_id = u.id
    JOIN kendaraan k ON b.kendaraan_id = k.id
    JOIN layanan l ON b.layanan_id = l.id
    ORDER BY b.created_at DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin â€“ GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderAdminSidebar('dashboard'); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard Admin</h1>
                <p class="page-subtitle">Pantau seluruh aktivitas bengkel GARAGEZKA secara real-time.</p>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card">
                <div class="stat-label">Total User</div>
                <div class="stat-value"><?= str_pad($totalUsers, 2, '0', STR_PAD_LEFT) ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Terdaftar</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Booking</div>
                <div class="stat-value"><?= str_pad($totalBooking, 2, '0', STR_PAD_LEFT) ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;"><?= $bookingHariIni ?> hari ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Booking Pending</div>
                <div class="stat-value" style="color: <?= $bookingPending > 0 ? '#f59e0b' : 'var(--text-primary)' ?>"><?= str_pad($bookingPending, 2, '0', STR_PAD_LEFT) ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Perlu dikonfirmasi</div>
            </div>
            <div class="stat-card" style="grid-column: span 2;">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value" style="font-size:1.4rem; color: var(--accent);"><?= formatRupiah($totalRevenue) ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Dari booking selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pesan Kontak</div>
                <div class="stat-value" style="color: <?= $pesanBaru > 0 ? 'var(--accent)' : 'var(--text-primary)' ?>"><?= str_pad($pesanBaru, 2, '0', STR_PAD_LEFT) ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Belum dibaca</div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
            <a href="bookings.php?status=pending" class="btn btn-primary btn-sm">
                â³ Konfirmasi Booking (<?= $bookingPending ?>)
            </a>
            <a href="users.php" class="btn btn-outline btn-sm">ðŸ‘¥ Lihat Semua User</a>
            <a href="layanan.php?action=tambah" class="btn btn-outline btn-sm">âž• Tambah Layanan</a>
            <?php if ($pesanBaru > 0): ?>
            <a href="kontak.php" class="btn btn-outline btn-sm" style="color:var(--accent); border-color:rgba(229,53,53,0.3);">
                ðŸ“© <?= $pesanBaru ?> Pesan Baru
            </a>
            <?php endif; ?>
        </div>

        <!-- BOOKING TERBARU -->
        <div class="table-wrapper">
            <div class="table-header">
                <span class="table-title">Booking Terbaru</span>
                <a href="bookings.php" class="btn btn-ghost btn-sm">Lihat Semua â†’</a>
            </div>
            <?php if (empty($recentBookings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">ðŸ“‹</div>
                <h3>Belum ada booking</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Kendaraan</th>
                        <th>Layanan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Biaya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;"><?= htmlspecialchars($b['nama_lengkap']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($b['nama_kendaraan']) ?> <span style="color:var(--text-muted); font-size:0.75rem;">(<?= htmlspecialchars($b['nomor_plat']) ?>)</span></td>
                        <td><?= htmlspecialchars($b['nama_layanan']) ?></td>
                        <td><?= date('d M Y', strtotime($b['tanggal'])) ?></td>
                        <td><?= getStatusBadge($b['status']) ?></td>
                        <td style="font-weight:600;"><?= formatRupiah($b['total_biaya']) ?></td>
                        <td>
                            <a href="bookings.php?update_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">Kelola</a>
                        </td>
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
