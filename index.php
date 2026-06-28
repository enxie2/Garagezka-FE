<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$currentPage = 'index';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GARAGEZKA – Servis Motor Jadi Lebih Mudah</title>
    <meta name="description" content="GARAGEZKA hadir untuk mengubah paradigma bengkel motor tradisional. Booking servis motor online dengan sistem digital yang transparan dan profesional.">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php renderNavbar('index'); ?>

    <!-- HERO -->
    <section class="hero">
        <div>
            <span class="hero-label">Servis Motor Profesional & Terpercaya</span>
            <h1>Servis Motor Jadi<br>Lebih Mudah</h1>
            <p>Booking servis tanpa antri. Dapatkan perawatan mesin standar kompetisi untuk performa motor maksimal dan tepat waktu.</p>
            <div class="hero-actions">
                <a href="<?= isset($_SESSION['user_id']) ? 'booking.php' : 'login.php' ?>" class="btn btn-primary btn-lg">Booking Sekarang</a>
                <a href="layanan.php" class="btn btn-outline btn-lg">Lihat Layanan</a>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="section" style="padding-top:0">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Cepat</h3>
                <p>Finalisasi operasional dengan sistem digital terlengkap untuk pengerjaan tepat waktu.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Terpercaya</h3>
                <p>Transparansi penuh pada setiap penggantian suku cadang dan detail biaya pengerjaan.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👨‍🔧</div>
                <h3>Profesional</h3>
                <p>Mekanik ahli dengan peralatan diagnostik terbaru untuk performa motor maksimal.</p>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="section" style="padding-top: 20px; padding-bottom: 90px;">
        <div class="cta-banner">
            <h2>Siap untuk performa terbaik?</h2>
            <a href="<?= isset($_SESSION['user_id']) ? 'booking.php' : 'daftar.php' ?>" class="btn-cta">Daftar Sekarang</a>
        </div>
    </section>

    <?php renderFooter(); ?>

    <style>
    .hero { background: radial-gradient(ellipse at center top, rgba(229,53,53,0.08) 0%, transparent 60%); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
