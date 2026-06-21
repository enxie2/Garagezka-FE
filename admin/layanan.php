<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
requireAdmin();

$conn = getConnection();
$msg = '';
$error = '';

// Handle delete
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM layanan WHERE id=$id");
    header('Location: layanan.php?deleted=1');
    exit;
}

// Handle toggle aktif
if (isset($_POST['toggle_id'])) {
    $id  = (int)$_POST['toggle_id'];
    $cur = $conn->query("SELECT is_active FROM layanan WHERE id=$id")->fetch_assoc()['is_active'];
    $new = $cur ? 0 : 1;
    $conn->query("UPDATE layanan SET is_active=$new WHERE id=$id");
    header('Location: layanan.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id']) && !isset($_POST['toggle_id'])) {
    $editId    = (int)($_POST['edit_id'] ?? 0);
    $nama      = trim($_POST['nama_layanan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga     = (float)str_replace(['.', ','], ['', '.'], $_POST['harga'] ?? 0);
    $icon      = trim($_POST['icon'] ?? 'service');
    $estimasi  = trim($_POST['estimasi_waktu'] ?? '1-2 jam');

    if (empty($nama) || $harga <= 0) {
        $error = 'Nama layanan dan harga harus diisi.';
    } else {
        if ($editId) {
            $stmt = $conn->prepare("UPDATE layanan SET nama_layanan=?,deskripsi=?,harga=?,icon=?,estimasi_waktu=? WHERE id=?");
            $stmt->bind_param("ssdssi", $nama, $deskripsi, $harga, $icon, $estimasi, $editId);
        } else {
            $stmt = $conn->prepare("INSERT INTO layanan (nama_layanan,deskripsi,harga,icon,estimasi_waktu) VALUES (?,?,?,?,?)");
            $stmt->bind_param("ssdss", $nama, $deskripsi, $harga, $icon, $estimasi);
        }
        if ($stmt->execute()) {
            header('Location: layanan.php?saved=1');
            exit;
        } else {
            $error = 'Gagal menyimpan data.';
        }
    }
}

$layananList = $conn->query("SELECT * FROM layanan ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

$editData = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $editData = $conn->query("SELECT * FROM layanan WHERE id=$eid")->fetch_assoc();
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'tambah' || $editData;

$iconOptions = ['oil'=>'🛢️ Ganti Oli','tune'=>'🔧 Tune Up','service'=>'🔩 Servis','electric'=>'⚡ Elektrik','tire'=>'🛞 Ban','brake'=>'🔴 Rem','wash'=>'✨ Cuci','overhaul'=>'⚙️ Overhaul'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan – Admin GARAGEZKA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderAdminSidebar('layanan'); ?>
    <main class="main-content">
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <h1 class="page-title">Kelola Layanan</h1>
                <p class="page-subtitle">Tambah, edit, dan kelola semua layanan servis.</p>
            </div>
            <a href="layanan.php?action=tambah" class="btn btn-primary">➕ Tambah Layanan</a>
        </div>

        <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">✅ Layanan berhasil disimpan.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-danger">🗑️ Layanan berhasil dihapus.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger">❌ <?= $error ?></div><?php endif; ?>

        <!-- FORM -->
        <?php if ($showForm): ?>
        <div class="card" style="margin-bottom:24px;">
            <h2 style="font-size:1rem; font-weight:700; margin-bottom:20px;">
                <?= $editData ? 'Edit Layanan' : 'Tambah Layanan Baru' ?>
            </h2>
            <form method="POST" action="layanan.php">
                <?php if ($editData): ?>
                <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Nama Layanan</label>
                        <input type="text" name="nama_layanan" class="form-control" required
                               value="<?= htmlspecialchars($editData['nama_layanan'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" required min="0" step="1000"
                               value="<?= $editData['harga'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Icon / Kategori</label>
                        <select name="icon" class="form-control">
                            <?php foreach ($iconOptions as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($editData['icon'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Waktu</label>
                        <input type="text" name="estimasi_waktu" class="form-control"
                               placeholder="1-2 jam"
                               value="<?= htmlspecialchars($editData['estimasi_waktu'] ?? '1-2 jam') ?>">
                    </div>
                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">Simpan Layanan</button>
                    <a href="layanan.php" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- TABEL LAYANAN -->
        <div class="table-wrapper">
            <div class="table-header">
                <span class="table-title">Semua Layanan (<?= count($layananList) ?>)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Layanan</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Estimasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($layananList as $l): 
                        $icons = ['oil'=>'🛢️','tune'=>'🔧','service'=>'🔩','electric'=>'⚡','tire'=>'🛞','brake'=>'🔴','wash'=>'✨','overhaul'=>'⚙️'];
                    ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:1.3rem;"><?= $icons[$l['icon']] ?? '🔧' ?></span>
                                <span style="font-weight:500;"><?= htmlspecialchars($l['nama_layanan']) ?></span>
                            </div>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-secondary); max-width:200px;"><?= htmlspecialchars(substr($l['deskripsi'],0,60)) ?>...</td>
                        <td style="font-weight:600; color:var(--accent);"><?= formatRupiah($l['harga']) ?></td>
                        <td style="font-size:0.85rem;"><?= htmlspecialchars($l['estimasi_waktu']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="toggle_id" value="<?= $l['id'] ?>">
                                <button type="submit" class="badge <?= $l['is_active'] ? 'badge-success' : 'badge-danger' ?>"
                                    style="background:none; border:1px solid; cursor:pointer; padding:4px 10px; border-radius:4px;">
                                    <?= $l['is_active'] ? '✅ Aktif' : '❌ Nonaktif' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <a href="layanan.php?edit=<?= $l['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus layanan ini?')">
                                    <input type="hidden" name="delete_id" value="<?= $l['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--accent); border-color:rgba(229,53,53,0.3);">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
