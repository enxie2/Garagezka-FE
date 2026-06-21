<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami â€“ GARAGEZKA</title>
    <meta name="description" content="Kenali lebih dekat GARAGEZKA, workshop spesialis motor dengan teknologi diagnostik modern dan mekanik ahli berpengalaman.">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php renderNavbar('tentang'); ?>

    <!-- ABOUT HERO -->
    <section class="about-hero">
        <div class="about-grid">
            <div>
                <span class="section-label">TENTANG KAMI</span>
                <h1 class="section-title">Modernisasi Performa Roda Dua.</h1>
                <p style="color: var(--text-secondary); font-size: 1rem; line-height: 1.8; margin-bottom: 28px;">
                    GARAGEZKA hadir untuk mengubah paradigma bengkel motor tradisional. Kami menggabungkan prinsip mekanik dengan teknologi modern untuk memberikan layanan yang transparan dan profesional bagi setiap pengendara.
                </p>
                <a href="layanan.php" class="btn btn-primary">Jelajahi Layanan â†’</a>
            </div>
            <div>
                <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 40px; text-align: center;">
                    <div style="font-size: 5rem; margin-bottom: 20px; opacity: 0.8;">ðŸï¸</div>
                    <div style="color: var(--accent); font-size: 0.75rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">Standarisasi Teknikal Level Industri</div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 12px; line-height: 1.6;">Setiap langkah servis didokumentasikan secara digital untuk keamanan dan ketenangan pikiran Anda.</div>
                </div>
            </div>
        </div>

        <!-- INFORMASI BENGKEL -->
        <h2 class="section-title" style="margin-bottom: 24px;">Informasi Bengkel</h2>
        <div class="info-cards">
            <div class="info-card">
                <h3>ðŸ—“ï¸ Sejarah Singkat</h3>
                <p>Berdiri kecil di tahun 2018, GARAGEZKA tumbuh dengan obsesi pada detail mesin yang lebih dalam dari bengkel umum, di mana Kami melihat kesenjangan antara bengkel rumah dan bengkel umum, di mana GARAGEZKA membangun sistem diagnosis digital pertama yang memungkinkan pelanggan melihat status kesehatan kendaraan mereka secara real-time.</p>
            </div>
            <div class="info-card">
                <h3>ðŸ“ Lokasi</h3>
                <p style="font-weight: 700; color: var(--accent); font-size: 0.9rem; margin-bottom: 10px;">Pusat Diagnosa GARAGEZKA</p>
                <p>Jl. Reforma Raya No. 88, Blok Mesin, Jakarta Selatan.</p>
                <br>
                <a href="https://maps.google.com" target="_blank" class="btn btn-outline btn-sm" style="display: inline-flex;">Buka di Maps</a>
            </div>
        </div>
    </section>

    <!-- KEUNGGULAN LAYANAN -->
    <section class="section" style="border-top: 1px solid var(--border); padding-top: 70px;">
        <div style="text-align: center; margin-bottom: 16px;">
            <h2 class="section-title">Keunggulan Layanan</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Mengapa profesional memilih GARAGEZKA.</p>
        </div>
        <div class="advantage-grid">
            <div class="advantage-card">
                <div class="advantage-icon">ðŸ”¬</div>
                <h3>Diagnosa Digital</h3>
                <p>Pemeriksaan sistematis menggunakan perangkat ECU diagnostik terbaru untuk akurasi tanpa celah.</p>
            </div>
            <div class="advantage-card">
                <div class="advantage-icon">ðŸ”©</div>
                <h3>Suku Cadang Asli</h3>
                <p>Kami hanya menggunakan komponen bergaransi yang tersertifikasi OEM dengan patokan resmi pabrikan.</p>
            </div>
            <div class="advantage-card">
                <div class="advantage-icon">â°</div>
                <h3>Penjadwalan Tepat</h3>
                <p>Sistem booking yang menjamin pengerjaan dimulai tepat waktu tanpa antrian panjang.</p>
            </div>
            <div class="advantage-card">
                <div class="advantage-icon">ðŸ“Š</div>
                <h3>Laporan Digital</h3>
                <p>Riwayat servis lengkap tersimpan secara digital dan dapat diakses melalui aplikasi kapan saja.</p>
            </div>
        </div>
    </section>

    <?php renderFooter(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
