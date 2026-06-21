<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
requireAdmin();

$conn = getConnection();

// Mark all as read
if (isset($_POST['mark_read'])) {
    $conn->query("UPDATE pesan_kontak SET is_read=1");
    header('Location: kontak.php');
    exit;
}

// Delete message
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM pesan_kontak WHERE id=$id");
    header('Location: kontak.php');
    exit;
}

// Mark single as read
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $conn->query("UPDATE pesan_kontak SET is_read=1 WHERE id=$id");
}

$pesanList  = $conn->query("SELECT * FROM pesan_kontak ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unreadCount = count(array_filter($pesanList, fn($p) => !$p['is_read']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak â€“ Admin GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderAdminSidebar('kontak'); ?>
    <main class="main-content">
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <h1 class="page-title">
                    Pesan Kontak
                    <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-new" style="margin-left:8px;"><?= $unreadCount ?> Baru</span>
                    <?php endif; ?>
                </h1>
                <p class="page-subtitle">Pesan masuk dari form kontak pengunjung website.</p>
            </div>
            <?php if ($unreadCount > 0): ?>
            <form method="POST">
                <button name="mark_read" class="btn btn-outline btn-sm">âœ… Tandai Semua Dibaca</button>
            </form>
            <?php endif; ?>
        </div>

        <div style="display:flex; flex-direction:column; gap:14px;">
            <?php if (empty($pesanList)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">ðŸ“©</div>
                <h3>Belum ada pesan masuk</h3>
            </div>
            <?php else: ?>
            <?php foreach ($pesanList as $p): ?>
            <div class="card" style="<?= !$p['is_read'] ? 'border-left: 3px solid var(--accent);' : 'border-left: 3px solid var(--border);' ?> padding: 20px 24px; display:flex; gap:16px; align-items:flex-start;">
                <div style="width:42px; height:42px; border-radius:50%; background: <?= !$p['is_read'] ? 'linear-gradient(135deg,#e53535,#7f1d1d)' : 'var(--bg-hover)' ?>; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem; flex-shrink:0;">
                    <?= strtoupper(substr($p['nama_lengkap'],0,1)) ?>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px; flex-wrap:wrap;">
                        <span style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($p['nama_lengkap']) ?></span>
                        <span style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($p['email']) ?></span>
                        <?php if (!$p['is_read']): ?>
                        <span class="badge badge-new" style="font-size:0.65rem;">BARU</span>
                        <?php endif; ?>
                        <span style="margin-left:auto; font-size:0.75rem; color:var(--text-muted);">
                            <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                        </span>
                    </div>
                    <p style="color:var(--text-secondary); font-size:0.875rem; line-height:1.6; margin:0 0 12px 0;">
                        <?= nl2br(htmlspecialchars($p['pesan'])) ?>
                    </p>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="mailto:<?= htmlspecialchars($p['email']) ?>" class="btn btn-primary btn-sm">
                            ðŸ“§ Balas via Email
                        </a>
                        <?php if (!$p['is_read']): ?>
                        <a href="kontak.php?read=<?= $p['id'] ?>" class="btn btn-outline btn-sm">âœ… Tandai Dibaca</a>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pesan ini?')">
                            <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--accent);border-color:rgba(229,53,53,0.3);">ðŸ—‘ï¸ Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
