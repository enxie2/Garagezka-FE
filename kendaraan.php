<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn   = getConnection();
$userId = $_SESSION['user_id'];
$error  = '';
$success = '';

// Handle delete
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM kendaraan WHERE id = $delId AND user_id = $userId");
    header('Location: kendaraan.php?deleted=1');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $editId  = (int)($_POST['edit_id'] ?? 0);
    $nama    = trim($_POST['nama_kendaraan'] ?? '');
    $plat    = trim($_POST['nomor_plat'] ?? '');
    $tahun   = (int)($_POST['tahun_produksi'] ?? 0);
    $warna   = trim($_POST['warna'] ?? '');

    if (empty($nama) || empty($plat) || empty($tahun)) {
        $error = 'Nama kendaraan, nomor plat, dan tahun produksi harus diisi.';
    } else {
        if ($editId) {
            $stmt = $conn->prepare("UPDATE kendaraan SET nama_kendaraan=?, nomor_plat=?, tahun_produksi=?, warna=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssissi", $nama, $plat, $tahun, $warna, $editId, $userId);
        } else {
            $stmt = $conn->prepare("INSERT INTO kendaraan (user_id, nama_kendaraan, nomor_plat, tahun_produksi, warna) VALUES (?,?,?,?,?)");
            $stmt->bind_param("issis", $userId, $nama, $plat, $tahun, $warna);
        }
        if ($stmt->execute()) {
            header('Location: kendaraan.php?saved=1');
            exit;
        } else {
            $error = 'Gagal menyimpan data.';
        }
    }
}

$kendaraanList = $conn->query("SELECT * FROM kendaraan WHERE user_id = $userId ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$totalKendaraan = count($kendaraanList);

// Check bengkel status (simple demo)
$bengkelStatus = 'Tersedia untuk Booking';

// Edit mode
$editData = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM kendaraan WHERE id = $eid AND user_id = $userId");
    $editData = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kendaraan Saya â€“ GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('kendaraan'); ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Kendaraan Saya</h1>
        </div>

        <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">âœ… Data kendaraan berhasil disimpan.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-danger">ðŸ—‘ï¸ Kendaraan berhasil dihapus.</div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger">âŒ <?= $error ?></div>
        <?php endif; ?>

        <!-- FORM ADD/EDIT -->
        <?php if (isset($_GET['tambah']) || $editData): ?>
        <div class="card" style="margin-bottom: 24px;">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px;">
                <?= $editData ? 'Edit Kendaraan' : 'Tambah Kendaraan Baru' ?>
            </h2>
            <form method="POST" action="kendaraan.php">
                <?php if ($editData): ?>
                <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Nama Kendaraan</label>
                        <input type="text" name="nama_kendaraan" class="form-control"
                               placeholder="Honda Vario 160" required
                               value="<?= htmlspecialchars($editData['nama_kendaraan'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Plat</label>
                        <input type="text" name="nomor_plat" class="form-control"
                               placeholder="B 1234 ABC" required
                               value="<?= htmlspecialchars($editData['nomor_plat'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tahun Produksi</label>
                        <input type="number" name="tahun_produksi" class="form-control"
                               placeholder="2022" min="1990" max="<?= date('Y') ?>" required
                               value="<?= $editData['tahun_produksi'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warna (opsional)</label>
                        <input type="text" name="warna" class="form-control"
                               placeholder="Hitam"
                               value="<?= htmlspecialchars($editData['warna'] ?? '') ?>">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">Simpan Kendaraan</button>
                    <a href="kendaraan.php" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- KENDARAAN GRID -->
        <div class="kendaraan-grid">
            <?php foreach ($kendaraanList as $k): ?>
            <div class="kendaraan-card">
                <div class="kendaraan-icon">ðŸï¸</div>
                <div class="kendaraan-name"><?= htmlspecialchars($k['nama_kendaraan']) ?></div>
                <div class="kendaraan-plat"><?= htmlspecialchars($k['nomor_plat']) ?></div>
                <div class="kendaraan-meta">TAHUN PRODUKSI</div>
                <div class="kendaraan-meta"><span><?= $k['tahun_produksi'] ?></span></div>
                <?php if ($k['warna']): ?>
                <div class="kendaraan-meta" style="margin-top: 4px;">Warna: <span><?= htmlspecialchars($k['warna']) ?></span></div>
                <?php endif; ?>
                <div class="kendaraan-actions">
                    <a href="kendaraan.php?edit=<?= $k['id'] ?>" class="btn btn-outline btn-sm" title="Edit">âœï¸</a>
                    <form method="POST" action="kendaraan.php" style="display: inline;"
                          onsubmit="return confirm('Hapus kendaraan ini?')">
                        <input type="hidden" name="delete_id" value="<?= $k['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-sm" title="Hapus" style="color: var(--accent); border-color: rgba(229,53,53,0.3);">ðŸ—‘ï¸</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- ADD CARD -->
            <a href="kendaraan.php?tambah=1" class="kendaraan-card kendaraan-add">
                <div class="kendaraan-add-icon">âž•</div>
                <div class="kendaraan-add-text">Tambah Kendaraan Baru</div>
                <div class="kendaraan-add-sub">Daftarkan motor baru kamu</div>
            </a>
        </div>

        <!-- STATS MINI -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 20px; max-width: 420px;">
            <div class="stat-card">
                <div class="stat-label">Total Kendaraan</div>
                <div class="stat-value"><?= str_pad($totalKendaraan, 2, '0', STR_PAD_LEFT) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Status Bengkel</div>
                <div style="font-size: 0.85rem; font-weight: 600; color: var(--success); margin-top: 8px;">âœ… <?= $bengkelStatus ?></div>
            </div>
        </div>
    </main>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
