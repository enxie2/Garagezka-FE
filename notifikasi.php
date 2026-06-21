<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn   = getConnection();
$userId = $_SESSION['user_id'];

// Mark all as read
if (isset($_POST['mark_read'])) {
    $conn->query("UPDATE notifikasi SET is_read = 1 WHERE user_id = $userId");
    header('Location: notifikasi.php');
    exit;
}

$notifList = $conn->query("SELECT * FROM notifikasi WHERE user_id = $userId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unreadCount = count(array_filter($notifList, fn($n) => !$n['is_read']));

// Group by date
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$grouped = [];
foreach ($notifList as $n) {
    $date = date('Y-m-d', strtotime($n['created_at']));
    if ($date === $today) {
        $grouped['Hari Ini'][] = $n;
    } elseif ($date === $yesterday) {
        $grouped['Kemarin'][] = $n;
    } else {
        $grouped['Sebelumnya'][] = $n;
    }
}

$notifIcons = [
    'berhasil' => '📅',
    'selesai'  => '🔧',
    'info'     => 'ℹ️',
    'peringatan' => '⚠️',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi – GARAGEZKA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('notifikasi'); ?>

    <main class="main-content">
        <div class="page-header">
            <div class="notif-header">
                <div>
                    <h1 class="page-title">
                        Notifikasi
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge badge-new" style="margin-left: 8px; font-size: 0.75rem;"><?= $unreadCount ?> BARU</span>
                        <?php endif; ?>
                    </h1>
                    <p class="page-subtitle">Kelola dan pantau semua aktivitas kendaraan Anda dalam satu dashboard.</p>
                </div>
                <?php if ($unreadCount > 0): ?>
                <form method="POST">
                    <button type="submit" name="mark_read" class="btn btn-ghost btn-sm" style="color: var(--accent);">
                        TANDAI SUDAH BACA
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-wrapper">
            <?php if (empty($notifList)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔔</div>
                <h3>Belum ada notifikasi</h3>
                <p>Notifikasi akan muncul setelah Anda melakukan booking.</p>
            </div>
            <?php else: ?>
            <?php foreach ($grouped as $label => $items): ?>
            <div class="notif-section-label">
                <span><?= $label ?></span>
                <?php if ($label === 'Hari Ini' && $unreadCount > 0): ?>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="mark_read" style="background: none; border: none; color: var(--accent); font-size: 0.72rem; font-weight: 600; cursor: pointer; letter-spacing: 0.5px;">
                        TANDAI SUDAH BACA
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php foreach ($items as $n): ?>
            <div class="notif-item" style="<?= !$n['is_read'] ? 'background: rgba(229,53,53,0.03);' : '' ?>">
                <div class="notif-icon <?= $n['tipe'] ?>">
                    <?= $notifIcons[$n['tipe']] ?? 'ℹ️' ?>
                </div>
                <div class="notif-content">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span class="badge badge-<?= $n['tipe'] === 'berhasil' ? 'success' : ($n['tipe'] === 'selesai' ? 'info' : 'info') ?>" style="font-size: 0.65rem;">
                            <?= strtoupper($n['tipe']) ?>
                        </span>
                        <span class="notif-time"><?= date('H:i', strtotime($n['created_at'])) ?> <?= $n['tipe'] === 'selesai' ? 'AM' : '' ?></span>
                    </div>
                    <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
                    <div class="notif-msg"><?= htmlspecialchars($n['pesan']) ?></div>
                </div>
                <?php if (!$n['is_read']): ?>
                <div style="width: 8px; height: 8px; background: var(--accent); border-radius: 50%; flex-shrink: 0; margin-top: 4px;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
