<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
requireAdmin();

$conn = getConnection();

$users = $conn->query("
    SELECT u.*,
        COUNT(DISTINCT k.id) AS total_kendaraan,
        COUNT(DISTINCT b.id) AS total_booking
    FROM users u
    LEFT JOIN kendaraan k ON k.user_id = u.id
    LEFT JOIN booking b ON b.user_id = u.id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User – Admin GARAGEZKA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderAdminSidebar('users'); ?>
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Kelola User</h1>
            <p class="page-subtitle">Daftar semua pengguna yang terdaftar di GARAGEZKA.</p>
        </div>

        <div class="table-wrapper">
            <div class="table-header">
                <span class="table-title">
                    Semua User
                    <span style="color:var(--text-muted); font-weight:400; margin-left:8px;">(<?= count($users) ?>)</span>
                </span>
            </div>
            <?php if (empty($users)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>Belum ada user terdaftar</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Kendaraan</th>
                        <th>Booking</th>
                        <th>Bergabung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td style="color:var(--text-muted);"><?= $i + 1 ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#e53535,#7f1d1d); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; flex-shrink:0;">
                                    <?= strtoupper(substr($u['nama_lengkap'],0,1)) ?>
                                </div>
                                <span style="font-weight:500;"><?= htmlspecialchars($u['nama_lengkap']) ?></span>
                            </div>
                        </td>
                        <td style="font-size:0.85rem;"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="font-size:0.85rem;"><?= htmlspecialchars($u['no_hp']) ?></td>
                        <td style="text-align:center;">
                            <span class="badge badge-info"><?= $u['total_kendaraan'] ?> unit</span>
                        </td>
                        <td style="text-align:center;">
                            <a href="bookings.php" style="color:var(--accent); font-weight:600;">
                                <?= $u['total_booking'] ?> booking
                            </a>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
