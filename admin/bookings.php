<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
requireAdmin();

$conn = getConnection();
$msg = '';

// Update status booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = (int)$_POST['booking_id'];
    $newStatus = $_POST['status'];
    $allowed   = ['pending', 'dikonfirmasi', 'selesai', 'dibatalkan'];
    if (in_array($newStatus, $allowed)) {
        $stmtUpdate = $conn->prepare("UPDATE booking SET status=? WHERE id=?");
        $stmtUpdate->bind_param("si", $newStatus, $bookingId);
        $stmtUpdate->execute();
        // Kirim notifikasi ke user
        $bk = $conn->query("SELECT user_id, tanggal FROM booking WHERE id=$bookingId")->fetch_assoc();
        if ($bk) {
            $labelMap = ['dikonfirmasi'=>'Booking Dikonfirmasi','selesai'=>'Servis Selesai','dibatalkan'=>'Booking Dibatalkan'];
            $pesanMap = [
                'dikonfirmasi' => "Booking Anda pada " . date('d M Y', strtotime($bk['tanggal'])) . " telah dikonfirmasi oleh bengkel.",
                'selesai'      => "Servis kendaraan Anda pada " . date('d M Y', strtotime($bk['tanggal'])) . " telah selesai.",
                'dibatalkan'   => "Mohon maaf, booking Anda pada " . date('d M Y', strtotime($bk['tanggal'])) . " telah dibatalkan.",
            ];
            if (isset($labelMap[$newStatus])) {
                $uid  = $bk['user_id'];
                $judul = $labelMap[$newStatus];
                $pesan = $pesanMap[$newStatus];
                $tipe  = ($newStatus === 'selesai') ? 'selesai' : ($newStatus === 'dibatalkan' ? 'info' : 'berhasil');
                $stmt = $conn->prepare("INSERT INTO notifikasi (user_id, booking_id, judul, pesan, tipe) VALUES (?,?,?,?,?)");
                $stmt->bind_param("iisss", $uid, $bookingId, $judul, $pesan, $tipe);
                $stmt->execute();
            }
        }
        $msg = 'Status booking berhasil diupdate.';
    }
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$where = $filterStatus ? "WHERE b.status='$filterStatus'" : '';

$bookings = $conn->query("
    SELECT b.*, u.nama_lengkap, u.no_hp, k.nama_kendaraan, k.nomor_plat, l.nama_layanan, l.estimasi_waktu
    FROM booking b
    JOIN users u ON b.user_id = u.id
    JOIN kendaraan k ON b.kendaraan_id = k.id
    JOIN layanan l ON b.layanan_id = l.id
    $where
    ORDER BY b.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$statusList = ['', 'pending', 'dikonfirmasi', 'selesai', 'dibatalkan'];
$labelStatus = ['' => 'Semua', 'pending' => 'Pending', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking – Admin GARAGEZKA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderAdminSidebar('bookings'); ?>
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Kelola Booking</h1>
            <p class="page-subtitle">Lihat dan ubah status semua booking dari seluruh user.</p>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-success">✅ <?= $msg ?></div>
        <?php endif; ?>

        <!-- FILTER STATUS -->
        <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
            <?php foreach ($statusList as $s): ?>
            <a href="bookings.php<?= $s ? '?status='.$s : '' ?>"
               class="btn btn-sm <?= $filterStatus === $s ? 'btn-primary' : 'btn-outline' ?>">
                <?= $labelStatus[$s] ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="table-wrapper">
            <div class="table-header">
                <span class="table-title">
                    <?= $filterStatus ? $labelStatus[$filterStatus] : 'Semua Booking' ?>
                    <span style="color:var(--text-muted); font-weight:400; margin-left:8px;">(<?= count($bookings) ?>)</span>
                </span>
            </div>
            <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>Tidak ada booking</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Kendaraan</th>
                        <th>Layanan</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Biaya</th>
                        <th>Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td style="color:var(--text-muted); font-size:0.75rem;">#<?= str_pad($b['id'],4,'0',STR_PAD_LEFT) ?></td>
                        <td>
                            <div style="font-weight:500; font-size:0.85rem;"><?= htmlspecialchars($b['nama_lengkap']) ?></div>
                            <div style="font-size:0.72rem; color:var(--text-muted);"><?= htmlspecialchars($b['no_hp']) ?></div>
                        </td>
                        <td>
                            <div style="font-size:0.85rem;"><?= htmlspecialchars($b['nama_kendaraan']) ?></div>
                            <div style="font-size:0.72rem; color:var(--text-muted);"><?= htmlspecialchars($b['nomor_plat']) ?></div>
                        </td>
                        <td style="font-size:0.85rem;"><?= htmlspecialchars($b['nama_layanan']) ?></td>
                        <td style="font-size:0.85rem;">
                            <?= date('d M Y', strtotime($b['tanggal'])) ?>
                            <div style="font-size:0.72rem; color:var(--text-muted);"><?= date('H:i', strtotime($b['jam'])) ?> WIB</div>
                        </td>
                        <td><?= getStatusBadge($b['status']) ?></td>
                        <td style="font-weight:600; font-size:0.85rem;"><?= formatRupiah($b['total_biaya']) ?></td>
                        <td>
                            <form method="POST" action="bookings.php<?= $filterStatus ? '?status='.$filterStatus : '' ?>" style="display:flex; gap:4px; flex-wrap:wrap;">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <?php
                                $transitions = [
                                    'pending'      => ['dikonfirmasi' => '✅ Konfirmasi', 'dibatalkan' => '❌ Batalkan'],
                                    'dikonfirmasi' => ['selesai' => '🏁 Selesai', 'dibatalkan' => '❌ Batalkan'],
                                    'selesai'      => [],
                                    'dibatalkan'   => [],
                                ];
                                foreach ($transitions[$b['status']] ?? [] as $statusVal => $label):
                                ?>
                                <button type="submit" name="update_status" value="1"
                                    onclick="this.form.status.value='<?= $statusVal ?>'"
                                    class="btn btn-sm <?= $statusVal === 'dibatalkan' ? 'btn-outline' : 'btn-primary' ?>"
                                    style="<?= $statusVal === 'dibatalkan' ? 'color:var(--accent);border-color:rgba(229,53,53,0.3);' : '' ?> font-size:0.72rem; padding:4px 8px;">
                                    <?= $label ?>
                                </button>
                                <?php endforeach; ?>
                                <input type="hidden" name="status" value="">
                            </form>
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
