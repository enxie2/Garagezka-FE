<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

$conn   = getConnection();
$userId = $_SESSION['user_id'];
$error  = '';

// Get user's vehicles
$kendaraanList = $conn->query("SELECT * FROM kendaraan WHERE user_id = $userId ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// Get all layanan
$layananList = $conn->query("SELECT * FROM layanan WHERE is_active = 1 ORDER BY id")->fetch_all(MYSQLI_ASSOC);

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kendaraanId = (int)($_POST['kendaraan_id'] ?? 0);
    $layananId   = (int)($_POST['layanan_id'] ?? 0);
    $tanggal     = $_POST['tanggal'] ?? '';
    $jam         = $_POST['jam'] ?? '';
    $catatan     = trim($_POST['catatan'] ?? '');

    if (!$kendaraanId || !$layananId || empty($tanggal) || empty($jam)) {
        $error = 'Semua field booking harus diisi.';
    } elseif (strtotime($tanggal) < strtotime('today')) {
        $error = 'Tanggal booking tidak boleh di masa lalu.';
    } else {
        // Get layanan price
        $lRes = $conn->query("SELECT harga FROM layanan WHERE id = $layananId")->fetch_assoc();
        $biayaServis = $lRes['harga'] ?? 0;
        $biayaAdmin  = 10000;
        $totalBiaya  = $biayaServis + $biayaAdmin;

        $stmt = $conn->prepare("INSERT INTO booking (user_id, kendaraan_id, layanan_id, tanggal, jam, catatan, biaya_servis, biaya_admin, total_biaya) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iiisssddd", $userId, $kendaraanId, $layananId, $tanggal, $jam, $catatan, $biayaServis, $biayaAdmin, $totalBiaya);

        if ($stmt->execute()) {
            $bookingId = $conn->insert_id;
            
            // Add notification
            $notifJudul = "Booking Servis Dikonfirmasi";
            $notifPesan = "Jadwal servis Anda pada $tanggal pukul $jam telah berhasil dibooking.";
            $notifTipe  = "berhasil";
            $notifStmt  = $conn->prepare("INSERT INTO notifikasi (user_id, booking_id, judul, pesan, tipe) VALUES (?,?,?,?,?)");
            $notifStmt->bind_param("iisss", $userId, $bookingId, $notifJudul, $notifPesan, $notifTipe);
            $notifStmt->execute();

            header("Location: booking-konfirmasi.php?id=$bookingId");
            exit;
        } else {
            $error = 'Gagal membuat booking. Silakan coba lagi.';
        }
    }
}

$iconMap = [
    'oil' => '🛢️',
    'tune' => '🔧',
    'service' => '🛠️',
    'electric' => '⚡',
    'tire' => '🛞',
    'brake' => '🛑',
    'wash' => '✨',
    'overhaul' => '⚙️',
];

$preselectedLayanan = (int)($_GET['layanan_id'] ?? 0);

// Time slots
$timeSlots = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Servis – GARAGEZKA</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php renderSidebar('booking'); ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Booking Servis</h1>
            <p class="page-subtitle">Atur jadwal servis motor Anda dengan cepat dan mudah di Garagezka.</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">❌ <?= $error ?></div>
        <?php endif; ?>

        <?php if (empty($kendaraanList)): ?>
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">🏍️</div>
                <h3>Belum ada kendaraan</h3>
                <p>Tambahkan kendaraan Anda terlebih dahulu sebelum booking.</p>
                <a href="kendaraan.php?tambah=1" class="btn btn-primary" style="margin-top: 16px;">Tambah Kendaraan</a>
            </div>
        </div>
        <?php else: ?>

        <form method="POST" action="booking.php" id="bookingForm">
            <div class="booking-layout">
                <div>
                    <!-- STEP 1: PILIH KENDARAAN -->
                    <div class="booking-step">
                        <div class="step-header">
                            <div class="step-number">1</div>
                            <div>
                                <div class="step-label">PILIH KENDARAAN</div>
                            </div>
                        </div>
                        <select name="kendaraan_id" id="kendaraan_id" class="form-control" required onchange="updateSummary()">
                            <option value="">-- Pilih Kendaraan --</option>
                            <?php foreach ($kendaraanList as $k): ?>
                            <option value="<?= $k['id'] ?>" data-nama="<?= htmlspecialchars($k['nama_kendaraan']) ?>" data-plat="<?= htmlspecialchars($k['nomor_plat']) ?>">
                                🏍️ <?= htmlspecialchars($k['nama_kendaraan']) ?> (<?= htmlspecialchars($k['nomor_plat']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- STEP 2: PILIH LAYANAN -->
                    <div class="booking-step">
                        <div class="step-header">
                            <div class="step-number">2</div>
                            <div>
                                <div class="step-label">KATEGORI LAYANAN</div>
                            </div>
                        </div>
                        <input type="hidden" name="layanan_id" id="layanan_id" value="<?= $preselectedLayanan ?>">
                        <div class="layanan-grid">
                            <?php foreach ($layananList as $l): ?>
                            <div class="layanan-option <?= ($preselectedLayanan == $l['id']) ? 'selected' : '' ?>"
                                 onclick="selectLayanan(<?= $l['id'] ?>, '<?= addslashes($l['nama_layanan']) ?>', <?= $l['harga'] ?>)"
                                 id="layanan-<?= $l['id'] ?>">
                                <div class="layanan-option-icon"><?= $iconMap[$l['icon']] ?? '🔧' ?></div>
                                <div class="layanan-option-name"><?= htmlspecialchars($l['nama_layanan']) ?></div>
                                <div class="layanan-option-desc"><?= htmlspecialchars(substr($l['deskripsi'], 0, 50)) ?>...</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- STEP 3: JADWAL -->
                    <div class="booking-step">
                        <div class="step-header">
                            <div class="step-number">3</div>
                            <div>
                                <div class="step-label">JADWAL KEDATANGAN</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <div class="form-label" style="margin-bottom: 10px;">PILIH TANGGAL</div>
                                <input type="date" name="tanggal" id="tanggal" class="form-control"
                                       min="<?= date('Y-m-d') ?>" required onchange="updateSummary()">
                            </div>
                            <div>
                                <div class="form-label" style="margin-bottom: 10px;">PILIH JAM</div>
                                <input type="hidden" name="jam" id="jam_hidden">
                                <div class="time-slots">
                                    <?php foreach ($timeSlots as $ts): ?>
                                    <div class="time-slot" onclick="selectTime('<?= $ts ?>')" id="ts-<?= str_replace(':', '', $ts) ?>">
                                        <?= $ts ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 18px;">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea name="catatan" class="form-control" rows="3"
                                      placeholder="Tuliskan kendala atau catatan khusus..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- RINGKASAN BOOKING -->
                <div class="booking-summary">
                    <div class="summary-title">RINGKASAN BOOKING</div>
                    <div class="summary-row">
                        <span class="summary-label">Kendaraan</span>
                        <span class="summary-value" id="sum-kendaraan">–</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Layanan</span>
                        <span class="summary-value" id="sum-layanan">–</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Jadwal</span>
                        <span class="summary-value" id="sum-jadwal">–</span>
                    </div>
                    <hr class="summary-divider">
                    <div class="summary-row">
                        <span class="summary-label">Biaya Jasa Servis</span>
                        <span class="summary-value" id="sum-biaya-servis">–</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Biaya Admin</span>
                        <span class="summary-value">Rp 10.000</span>
                    </div>
                    <hr class="summary-divider">
                    <div class="summary-total">
                        <span>Total Biaya</span>
                        <span class="summary-total-value" id="sum-total">–</span>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin-bottom: 16px;" id="sum-estimasi"></div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg" id="btn-booking">Lanjut Booking</button>
                    <p style="font-size: 0.72rem; color: var(--text-muted); text-align: center; margin-top: 12px; line-height: 1.5;">
                        Dengan mengklik tombol di atas, Anda menyetujui syarat dan ketentuan layanan GARAGEZKA.
                    </p>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </main>
</div>

<script>
let selectedLayanan = { id: <?= $preselectedLayanan ?>, nama: '', harga: 0 };
let selectedTime = '';

<?php if ($preselectedLayanan): ?>
(function() {
    <?php
    foreach ($layananList as $l) {
        if ($l['id'] == $preselectedLayanan) {
            echo "selectLayanan({$l['id']}, '" . addslashes($l['nama_layanan']) . "', {$l['harga']});";
            break;
        }
    }
    ?>
})();
<?php endif; ?>

function selectLayanan(id, nama, harga) {
    document.querySelectorAll('.layanan-option').forEach(el => el.classList.remove('selected'));
    document.getElementById('layanan-' + id).classList.add('selected');
    document.getElementById('layanan_id').value = id;
    selectedLayanan = { id, nama, harga };
    updateSummary();
}

function selectTime(time) {
    document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
    const tsId = 'ts-' + time.replace(':', '');
    document.getElementById(tsId).classList.add('selected');
    document.getElementById('jam_hidden').value = time;
    selectedTime = time;
    updateSummary();
}

function updateSummary() {
    const kendaraanSel = document.getElementById('kendaraan_id');
    const tanggal = document.getElementById('tanggal').value;

    if (kendaraanSel.value) {
        const opt = kendaraanSel.selectedOptions[0];
        document.getElementById('sum-kendaraan').textContent = opt.dataset.nama + ' (' + opt.dataset.plat + ')';
    }

    if (selectedLayanan.id) {
        document.getElementById('sum-layanan').textContent = selectedLayanan.nama;
        document.getElementById('sum-biaya-servis').textContent = formatRp(selectedLayanan.harga);
        document.getElementById('sum-total').textContent = formatRp(selectedLayanan.harga + 10000);
    }

    if (tanggal && selectedTime) {
        document.getElementById('sum-jadwal').textContent = formatDate(tanggal) + ', ' + selectedTime;
    }
}

function formatRp(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
}

function formatDate(d) {
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    const dt = new Date(d);
    return dt.getDate() + ' ' + months[dt.getMonth()] + ' ' + dt.getFullYear();
}
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
