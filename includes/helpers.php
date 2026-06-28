<?php
function renderNavbar($currentPage = '') {
    $isLoggedIn = isset($_SESSION['user_id']);
    $pages = [
        'index' => ['Beranda', 'index.php'],
        'tentang' => ['Tentang Kami', 'tentang.php'],
        'layanan' => ['Layanan', 'layanan.php'],
        'kontak' => ['Kontak', 'kontak.php'],
    ];
    echo '<style>
    .navbar-nav {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 32px !important;
    }
    @media (max-width: 768px) {
        .navbar-nav {
            display: none !important;
        }
    }
    </style>';
    echo '<nav class="navbar">';
    echo '<a href="index.php" class="navbar-brand">GARAGE<span>ZKA</span></a>';
    echo '<div class="navbar-nav">';
    foreach ($pages as $key => [$label, $href]) {
        $active = ($currentPage === $key) ? ' active' : '';
        echo "<a href=\"$href\" class=\"$active\">$label</a>";
    }
    echo '</div>';
    echo '<div class="navbar-actions">';
    if ($isLoggedIn) {
        echo '<a href="dashboard.php" class="btn btn-outline btn-sm">Dashboard</a>';
    } else {
        echo '<a href="login.php" class="btn-login">Login</a>';
        echo '<a href="daftar.php" class="btn btn-primary btn-sm">Daftar</a>';
    }
    echo '</div>';
    echo '</nav>';
}

function renderFooter() {
    echo '
    <footer class="footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">GARAGE<span>ZKA</span></div>
                <p class="footer-desc">Workshop spesialis motor dengan dedikasi pada akurasi teknis dan kepuasan pelanggan. Kami menghadirkan layanan servis berkualitas tinggi dengan teknologi digital untuk transparansi penuh.</p>
            </div>
            <div>
                <div class="footer-nav-title">Navigasi</div>
                <nav class="footer-nav">
                    <a href="index.php">Beranda</a>
                    <a href="tentang.php">Tentang Kami</a>
                    <a href="layanan.php">Layanan</a>
                    <a href="kontak.php">Kontak</a>
                </nav>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; ' . date('Y') . ' GARAGEZKA. Presisi dalam Performa.
        </div>
    </footer>';
}

function renderSidebar($activePage = '') {
    $userName = $_SESSION['user_name'] ?? 'User';
    $userId = $_SESSION['user_id'] ?? 0;
    $initial = strtoupper(substr($userName, 0, 1));
    
    $navItems = [
        'dashboard' => ['Dashboard', 'dashboard.php', '
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>'],
        'booking' => ['Booking Servis', 'booking.php', '
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>'],
        'kendaraan' => ['Kendaraan Saya', 'kendaraan.php', '
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
            </svg>'],
        'riwayat' => ['Riwayat Servis', 'riwayat.php', '
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>'],
        'notifikasi' => ['Notifikasi', 'notifikasi.php', '
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>'],
        'profil' => ['Profil', 'profil.php', '
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>'],
    ];
    
    echo '<aside class="sidebar">';
    echo '<div class="sidebar-logo">GARAGE<span>ZKA</span></div>';
    echo '<nav class="sidebar-nav">';
    
    foreach ($navItems as $key => [$label, $href, $icon]) {
        $active = ($activePage === $key) ? ' active' : '';
        echo "<a href=\"$href\" class=\"$active\">{$icon}{$label}</a>";
    }
    
    echo '</nav>';
    echo '<div class="sidebar-user">';
    echo "<div class=\"sidebar-avatar\">{$initial}</div>";
    echo '<div class="sidebar-user-info">';
    echo "<div class=\"sidebar-user-name\">{$userName}</div>";
    echo '<div class="sidebar-user-role">Member</div>';
    echo '</div>';
    echo '<a href="logout.php" class="sidebar-logout" title="Keluar">';
    echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
    echo '</a>';
    echo '</div>';
    echo '</aside>';
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
    if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getStatusBadge($status) {
    $map = [
        'pending'     => ['warning', 'Menunggu'],
        'dikonfirmasi'=> ['info', 'Dikonfirmasi'],
        'selesai'     => ['success', 'Selesai'],
        'dibatalkan'  => ['danger', 'Dibatalkan'],
    ];
    [$type, $label] = $map[$status] ?? ['info', $status];
    return "<span class=\"badge badge-{$type}\">{$label}</span>";
}

function renderAdminSidebar($activePage = '') {
    $userName = $_SESSION['user_name'] ?? 'Admin';
    $initial  = strtoupper(substr($userName, 0, 1));

    $navItems = [
        'dashboard' => ['Dashboard', 'dashboard.php',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>'],
        'bookings' => ['Kelola Booking', 'bookings.php',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'],
        'users' => ['Kelola User', 'users.php',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
        'layanan' => ['Kelola Layanan', 'layanan.php',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>'],
        'kontak' => ['Pesan Kontak', 'kontak.php',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'],
    ];

    echo '<aside class="sidebar admin-sidebar">';
    echo '<div class="sidebar-logo">GARAGE<span>ZKA</span><div class="admin-badge">ADMIN</div></div>';
    echo '<nav class="sidebar-nav">';
    foreach ($navItems as $key => [$label, $href, $icon]) {
        $active = ($activePage === $key) ? ' active' : '';
        echo "<a href=\"$href\" class=\"$active\">{$icon}{$label}</a>";
    }
    echo '</nav>';
    echo '<div class="sidebar-divider"></div>';
    echo '<div class="sidebar-nav" style="padding: 0 12px 12px;">';
    echo '<a href="../dashboard.php" style="font-size:0.8rem; color: var(--text-muted);">';
    echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>';
    echo 'Lihat sebagai User</a>';
    echo '</div>';
    echo '<div class="sidebar-user">';
    echo "<div class=\"sidebar-avatar\" style=\"background: linear-gradient(135deg, #e53535, #b91c1c);\">{$initial}</div>";
    echo '<div class="sidebar-user-info">';
    echo "<div class=\"sidebar-user-name\">{$userName}</div>";
    echo '<div class="sidebar-user-role" style="color: var(--accent);">Administrator</div>';
    echo '</div>';
    echo '<a href="../logout.php" class="sidebar-logout" title="Keluar">';
    echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
    echo '</a>';
    echo '</div>';
    echo '</aside>';
}
?>
