<?php

session_start();

include 'config/koneksi.php';


// TOTAL HEWAN
$totalHewan = mysqli_query($conn, "
SELECT COUNT(*) as total 
FROM hewan
");

$jumlahHewan = mysqli_fetch_assoc($totalHewan)['total'];


// TOTAL PETERNAK
$totalPeternak = mysqli_query($conn, "
SELECT COUNT(*) as total 
FROM users
WHERE role='peternak'
");

$jumlahPeternak = mysqli_fetch_assoc($totalPeternak)['total'];


// TOTAL DESA
$totalDesa = mysqli_query($conn, "
SELECT COUNT(DISTINCT alamat) as total
FROM users
WHERE role='peternak'
");

$jumlahDesa = mysqli_fetch_assoc($totalDesa)['total'];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TernakApp - Marketplace Hewan Kurban</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- PWA Settings -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#198754">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TernakApp">
    <link rel="apple-touch-icon" href="images/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="images/icons/icon-192.png">

    <!-- style -->
    <link rel="stylesheet" href="style.css">

</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">

            <a class="navbar-brand" href="#">
                🐄TernakApp
            </a>

            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>

                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">


                        <?php if (isset($_SESSION['user'])) { ?>

                            <?php if ($_SESSION['user']['role'] == 'admin') { ?>
                                <a href="dashboard/admin.php" class="btn btn-success">
                                    Dashboard Admin
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['user']['role'] == 'peternak') { ?>
                                <a href="dashboard/user.php" class="btn btn-success">
                                    Dashboard Peternak
                                </a>
                            <?php } ?>


                        <?php } else { ?>

                            <!-- hanya muncul kalau BELUM login -->
                            <button class="btn-login"
                                data-bs-toggle="modal"
                                data-bs-target="#authModal">
                                Login
                            </button>

                        <?php } ?>
                    </li>

                    <li class="nav-item ms-lg-2 mt-3 mt-lg-0" id="pwaInstallNav" style="display: none;">
                        <button id="pwaInstallBtn" class="btn btn-outline-success d-flex align-items-center gap-1">
                            <i class="bi bi-download"></i> Install App
                        </button>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- HERO -->
    <section class="hero" id="home">
        <div class="hero-glow"></div>
        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-7">

                    <span class="hero-eyebrow"><i class="bi bi-award"></i> Peternak Terpercaya &middot; Klaten</span>

                    <h1 class="hero-title">
                        Temukan Hewan Ternak Langsung Dari Peternak
                    </h1>

                    <p class="hero-subtitle">
                        Tanpa perantara &middot; Harga transparan &middot; Langsung chat WhatsApp
                    </p>

                    <div class="mt-4 d-flex flex-wrap gap-3">
                        <a href="home.php#marketplace" class="btn btn-green">
                            Lihat Marketplace
                        </a>

                        <a href="home.php#feature" class="btn btn-outline-custom">
                            Fitur Peternak
                        </a>
                    </div>

                </div>

            </div>

        </div>

        </div>
    </section>

    <!-- MARKETPLACE -->
    <section class="py-5" id="marketplace">

        <div class="container py-5">

            <div class="text-center mb-5">
                <span class="section-eyebrow">Koleksi Pilihan</span>
                <h2 class="section-title">Marketplace Hewan</h2>
                <p class="section-subtitle">
                    Pilih hewan Ternak terbaik langsung dari peternak terpercaya
                </p>

                <!-- SEARCH -->
                <div class="search-box shadow-lg">


                    <form method="GET" action="home.php#marketplace" id="filterForm">

                        <div class="row g-2 align-items-center justify-content-center">

                            <!-- Desa -->
                            <div class="col-xl col-lg-2 col-md-4">

                                <select name="lokasi" id="lokasi" class="form-select live-filter">

                                    <option value="">Semua Dusun</option>

                                    <?php
                                    $desa = [
                                        "Tasikmadu",
                                        "Keputran",
                                        "Kembang",
                                        "Kalikajar",
                                        "Mancasan",
                                        "Karang Kembang",
                                        "Karang Soko",
                                        "Tegalsoko",
                                        "Tegal Serut",
                                        "Banjaran",
                                        "Gatak",
                                        "Papringan",
                                        "Badran",
                                        "Santan",
                                        "Kadipolo",
                                        "Ngrendet"
                                    ];

                                    foreach ($desa as $d) {
                                        $selected = (isset($_GET['lokasi']) && $_GET['lokasi'] == $d) ? "selected" : "";
                                        echo "<option value='$d' $selected>$d</option>";
                                    }
                                    ?>

                                </select>

                            </div>

                            <!-- Hewan -->
                            <div class="col-xl col-lg-2 col-md-4">

                                <select id="hewan" name="hewan" class="form-select live-filter">

                                    <option value="">Pilih Hewan</option>
                                    <option value="Sapi" <?= (isset($_GET['hewan']) && $_GET['hewan'] === 'Sapi') ? 'selected' : ''; ?>>Sapi</option>
                                    <option value="Kambing" <?= (isset($_GET['hewan']) && $_GET['hewan'] === 'Kambing') ? 'selected' : ''; ?>>Kambing</option>
                                    <option value="Domba" <?= (isset($_GET['hewan']) && $_GET['hewan'] === 'Domba') ? 'selected' : ''; ?>>Domba</option>
                                    <option value="Burung" <?= (isset($_GET['hewan']) && $_GET['hewan'] === 'Burung') ? 'selected' : ''; ?>>Burung</option>
                                    <option value="Ayam" <?= (isset($_GET['hewan']) && $_GET['hewan'] === 'Ayam') ? 'selected' : ''; ?>>Ayam</option>

                                </select>

                            </div>

                            <!-- Ras -->
                            <div class="col-xl col-lg-2 col-md-4">

                                <select class="form-select live-filter" name="ras" id="ras">

                                    <option value="">Pilih Ras</option>
                                    <?php if (!empty($_GET['ras'])): ?>
                                        <option value="<?= htmlspecialchars($_GET['ras']); ?>" selected><?= htmlspecialchars($_GET['ras']); ?></option>
                                    <?php endif; ?>

                                </select>

                            </div>

                            <div class="col-xl col-lg-2 col-md-4">

                                <select name="peternak" id="peternak" class="form-select live-filter">

                                    <option value="">Semua Peternak</option>

                                    <?php

                                    $peternak = mysqli_query($conn, "
            SELECT id, nama 
            FROM users
            WHERE role='peternak'
            ORDER BY nama ASC
        ");

                                    while ($p = mysqli_fetch_assoc($peternak)) {
                                        $selected = (isset($_GET['peternak']) && (int)$_GET['peternak'] === (int)$p['id']) ? "selected" : "";
                                    ?>

                                        <option value="<?= $p['id'] ?>" <?= $selected; ?>>
                                            <?= htmlspecialchars($p['nama']); ?>
                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                            <!-- Tombol Reset / Submit -->
                            <div class="col-xl col-lg-2 col-md-4 d-flex gap-2">

                                <button type="submit" class="btn btn-green w-100" title="Cari">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" id="btnResetFilter" class="btn btn-outline-secondary" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>

                            </div>

                        </div>

                    </form>

                </div>
            </div>

            <!-- LOADER -->
            <div id="marketplaceLoader" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
                <p class="text-muted mt-2 fw-semibold">Memuat daftar hewan...</p>
            </div>

            <!-- CARD GRID (AJAX Content) -->
            <div class="row g-4" id="marketplaceGrid">
                <?php include 'ajax_marketplace.php'; ?>
            </div>

        </div>

    </section>

    </section>

    <!-- FEATURES -->
    <section class="pb-5" id="feature">

        <div class="container pb-5">

            <div class="text-center mb-5">

                <span class="section-eyebrow">
                    Keunggulan TernakApp
                </span>

                <h2 class="section-title">
                    Solusi Mudah Mengelola dan Menjual Hewan Ternak
                </h2>

                <p class="section-subtitle">
                    TernakApp membantu peternak memasarkan hewan kurban,
                    mengelola data ternak, serta mempertemukan pembeli
                    langsung dengan peternak terpercaya.
                </p>

            </div>


            <div class="row g-4">


                <!-- 1 -->
                <div class="col-lg-4 col-md-6">

                    <div class="feature-box">

                        <div class="feature-icon mb-4">
                            <i class="bi bi-whatsapp"></i>
                        </div>

                        <h4 class="fw-bold">
                            Chat Langsung
                        </h4>

                        <p class="text-muted">
                            Pembeli dapat menghubungi peternak langsung melalui WhatsApp tanpa perantara.
                        </p>

                    </div>

                </div>


                <!-- 2 -->
                <div class="col-lg-4 col-md-6">

                    <div class="feature-box">

                        <div class="feature-icon mb-4">
                            <i class="bi bi-clipboard-data"></i>
                        </div>

                        <h4 class="fw-bold">
                            Pencatatan Ternak
                        </h4>

                        <p class="text-muted">
                            Peternak dapat mencatat data hewan, berat, pengeluaran, dan perkembangan ternak.
                        </p>

                    </div>

                </div>


                <!-- 3 -->
                <div class="col-lg-4 col-md-6">

                    <div class="feature-box">

                        <div class="feature-icon mb-4">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <h4 class="fw-bold">
                            Peternak Terpercaya
                        </h4>

                        <p class="text-muted">
                            Data peternak tersimpan dengan jelas sehingga pembeli lebih yakin melakukan transaksi.
                        </p>

                    </div>

                </div>



                <!-- 4 -->
                <div class="col-lg-4 col-md-6">

                    <div class="feature-box">

                        <div class="feature-icon mb-4">
                            <i class="bi bi-geo-alt"></i>
                        </div>

                        <h4 class="fw-bold">
                            Lokasi Jelas
                        </h4>

                        <p class="text-muted">
                            Pembeli dapat mengetahui lokasi peternak sehingga memudahkan pengecekan hewan.
                        </p>

                    </div>

                </div>



                <!-- 5 -->
                <div class="col-lg-4 col-md-6">

                    <div class="feature-box">

                        <div class="feature-icon mb-4">
                            <i class="bi bi-tags"></i>
                        </div>

                        <h4 class="fw-bold">
                            Harga Transparan
                        </h4>

                        <p class="text-muted">
                            Informasi harga ditampilkan secara terbuka sehingga pembeli lebih mudah memilih.
                        </p>

                    </div>

                </div>



                <!-- 6 -->
                <div class="col-lg-4 col-md-6">

                    <div class="feature-box">

                        <div class="feature-icon mb-4">
                            <i class="bi bi-card-list"></i>
                        </div>

                        <h4 class="fw-bold">
                            Informasi Lengkap
                        </h4>

                        <p class="text-muted">
                            Setiap hewan memiliki informasi lengkap mulai dari jenis, ras, berat, dan harga.
                        </p>

                    </div>

                </div>


            </div>

        </div>

    </section>

    <!-- TENTANG -->
    <section class="py-5" id="tentang">

        <div class="container py-5">

            <div class="row align-items-center g-5">


                <!-- KIRI -->
                <div class="col-lg-6">

                    <span class="section-eyebrow">
                        Tentang TernakApp
                    </span>

                    <h2 class="section-title mb-4">
                        Menghubungkan Peternak Lokal Dengan Pembeli Secara Langsung
                    </h2>


                    <p class="text-muted fs-5">

                        TernakApp merupakan marketplace hewan kurban berbasis web
                        yang membantu mempertemukan pembeli dengan peternak secara langsung.
                        Melalui sistem ini, peternak dapat memasarkan hewan ternaknya
                        dengan lebih mudah, sedangkan pembeli dapat memperoleh informasi
                        hewan secara lengkap dan terpercaya.

                    </p>


                    <p class="text-muted fs-5">

                        Mulai dari informasi jenis hewan, ras, berat, harga,
                        hingga lokasi peternak tersedia dalam satu platform sehingga
                        proses pencarian hewan kurban menjadi lebih praktis,
                        transparan, dan terpercaya.

                    </p>


                    <a href="#marketplace" class="btn btn-green mt-3">

                        <i class="bi bi-search"></i>
                        Cari Hewan Sekarang

                    </a>


                </div>



                <!-- KANAN -->
                <div class="col-lg-6">


                    <div class="about-card">


                        <div class="about-item">

                            <div class="about-icon">
                                <i class="bi bi-shop"></i>
                            </div>

                            <div>
                                <h5>
                                    Marketplace Lokal
                                </h5>

                                <p>
                                    Mempertemukan pembeli dengan peternak sekitar Klaten.
                                </p>
                            </div>

                        </div>



                        <div class="about-item">

                            <div class="about-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>

                            <div>
                                <h5>
                                    Informasi Lengkap
                                </h5>

                                <p>
                                    Data hewan tersedia mulai jenis, ras, berat, dan harga.
                                </p>

                            </div>

                        </div>




                        <div class="about-item">

                            <div class="about-icon">
                                <i class="bi bi-people"></i>
                            </div>

                            <div>
                                <h5>
                                    Mendukung Peternak
                                </h5>

                                <p>
                                    Membantu peternak memperluas pemasaran hewan ternak.
                                </p>

                            </div>

                        </div>


                    </div>


                </div>


            </div>


            <!-- STATISTIK -->

            <div class="row mt-5 g-4">


                <div class="col-md-4">

                    <div class="about-stat">

                        <h2>
                            <?= $jumlahHewan ?>+
                        </h2>

                        <p>
                            Data Hewan Ternak
                        </p>

                    </div>

                </div>



                <div class="col-md-4">

                    <div class="about-stat">

                        <h2>
                            <?= $jumlahDesa ?>+
                        </h2>

                        <p>
                            Desa Terjangkau
                        </p>

                    </div>

                </div>



                <div class="col-md-4">

                    <div class="about-stat">

                        <h2>
                            <?= $jumlahPeternak ?>+
                        </h2>

                        <p>
                            Peternak Terdaftar
                        </p>

                    </div>

                </div>



            </div>


        </div>

    </section>

    <!-- CTA -->
    <section class="container pb-5">

        <div class="cta-section text-center">

            <h2 class="fw-bold display-5">
                Punya Ternak Untuk Dijual?
            </h2>

            <p class="mt-3 mb-4">
                Gabung menjadi peternak dan mulai pasarkan hewan kurban Anda.
            </p>

            <button class="btn btn-light btn-lg px-5" data-bs-toggle="modal" data-bs-target="#authModal">
                Daftar Sekarang
            </button>

        </div>

    </section>


    <!-- FOOTER -->
    <footer class="site-footer">

        <div class="footer-terrace">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,64 C240,10 480,110 720,58 C960,6 1200,96 1440,48 L1440,120 L0,120 Z" class="terrace-layer terrace-1"></path>
                <path d="M0,90 C240,50 480,120 720,84 C960,48 1200,110 1440,80 L1440,120 L0,120 Z" class="terrace-layer terrace-2"></path>
                <path d="M0,108 C240,86 480,120 720,104 C960,88 1200,120 1440,102 L1440,120 L0,120 Z" class="terrace-layer terrace-3"></path>
            </svg>
        </div>

        <div class="container footer-main">

            <div class="row g-5">

                <div class="col-lg-4 col-md-6">
                    <a class="footer-brand" href="#home">
                        <i class="bi bi-gem"></i> TernakApp
                    </a>
                    <p class="footer-desc">
                        Marketplace hewan kurban tepercaya yang menghubungkan Anda
                        langsung dengan peternak asli di Klaten &mdash; tanpa
                        perantara, dengan harga jujur dan kualitas terjaga.
                    </p>

                    <div class="footer-socials">
                        <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Navigasi</h6>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#marketplace">Marketplace</a></li>
                        <li><a href="#tentang">Tentang</a></li>
                        <li><a href="#fitur">Fitur Peternak</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-heading">Jenis Hewan</h6>
                    <ul class="footer-links">
                        <li><a href="#marketplace">Sapi Kurban</a></li>
                        <li><a href="#marketplace">Kambing Kurban</a></li>
                        <li><a href="#marketplace">Domba Kurban</a></li>
                        <li><a href="#marketplace">Ayam &amp; Unggas</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-heading">Hubungi Kami</h6>
                    <ul class="footer-links footer-contact">
                        <li><i class="bi bi-geo-alt"></i> Kemalang, Klaten, Jawa Tengah</li>
                        <li><i class="bi bi-whatsapp"></i> +62 812-0000-0000</li>
                        <li><i class="bi bi-envelope"></i> halo@ternakapp.id</li>
                    </ul>
                </div>

            </div>

            <div class="footer-divider"></div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TernakApp &mdash; Kemalang, Klaten. Seluruh hak cipta dilindungi.</p>
                <div class="footer-legal">
                    <a href="#">Kebijakan Privasi</a>
                    <span>&middot;</span>
                    <a href="#">Syarat &amp; Ketentuan</a>
                </div>
            </div>

        </div>

    </footer>


    <!-- MODAL LOGIN REGISTER -->
    <div class="modal fade" id="authModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">

                <div class="row g-0">

                    <div class="col-lg-5 d-none d-lg-flex bg-success text-white p-5 flex-column justify-content-center">
                        <h2 class="fw-bold">
                            TernakApp
                        </h2>

                        <p class="mt-3">
                            Kelola ternak, pencatatan, dan penjualan dalam satu dashboard.
                        </p>
                    </div>

                    <div class="col-lg-7 p-4 p-lg-5">

                        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                            <li class="nav-item w-50">
                                <button class="nav-link active w-100" data-bs-toggle="pill" data-bs-target="#loginTab">
                                    login
                                </button>
                            </li>

                            <li class="nav-item w-50">
                                <button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#registerTab">
                                    register
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">

                            <!-- LOGIN -->
                            <div class="tab-pane fade show active" id="loginTab">

                                <form action="auth/login.php" method="POST">

                                    <div class="mb-3">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>

                                    <button type="submit" name="login" class="btn btn-success w-100 py-3">
                                        Masuk Dashboard
                                    </button>

                                </form>

                            </div>

                            <!-- REGISTER -->
                            <div class="tab-pane fade" id="registerTab">

                                <form action="auth/register.php" method="POST">

                                    <div class="mb-3">
                                        <label>Nama</label>
                                        <input type="text" name="nama" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>No WhatsApp</label>
                                        <input type="text" name="wa" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Dusun</label>
                                        <input type="text" name="dusun" class="form-control"
                                            placeholder="Contoh: Tasikmadu" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control"
                                            placeholder="Contoh: Keputran" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control"
                                            value="Kemalang" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label>Kabupaten</label>
                                        <input type="text" name="kabupaten" class="form-control"
                                            value="Klaten" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>

                                    <button type="submit" name="register" class="btn btn-success w-100 py-3">
                                        Daftar
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.addEventListener('scroll', function() {

            const navbar = document.querySelector('.navbar');

            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }

        });

        // tilt 3D ringan untuk kartu hewan
        document.querySelectorAll('.animal-card').forEach(function(card) {
            card.addEventListener('mousemove', function(e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const rotateX = ((y / rect.height) - 0.5) * -8;
                const rotateY = ((x / rect.width) - 0.5) * 8;
                card.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            });
            card.addEventListener('mouseleave', function() {
                card.style.transform = '';
            });
        });

        const dataRas = {

            "Sapi": [
                "Limosin",
                "Simental",
                "Jawa",
                "Brahman",
                "Bali",
                "Madura"
            ],

            "Kambing": [
                "Jawa",
                "Etawa",
                "Kacang",
                "Boer",
                "Boerka"
            ],

            "Domba": [
                "Garut",
                "Merino",
                "Texel",
                "Ekor Tipis"
            ],


            "Ayam": [
                "Kampung",
                "Bangkok",
                "Cemani"
            ],

            "Burung": [
                "Murai",
                "Lovebird",
                "Kenari",
                "Perkutut",
                "Kacer",
                "Kenari",
                "Prenjak",
                "cucakhijau"
            ]

        };

        const hewan = document.getElementById("hewan");
        const ras = document.getElementById("ras");

        hewan.onchange = function() {

            ras.innerHTML = "<option value=''>Pilih Ras</option>";

            if (dataRas[this.value]) {

                dataRas[this.value].forEach(function(item) {

                    ras.innerHTML += `<option value="${item}">${item}</option>`;

                });

            }

            fetchMarketplace(1);

        };

        // ==========================================
        // Live AJAX Search & Filter Marketplace
        // ==========================================
        const filterForm = document.getElementById('filterForm');
        const marketplaceGrid = document.getElementById('marketplaceGrid');
        const marketplaceLoader = document.getElementById('marketplaceLoader');
        const btnResetFilter = document.getElementById('btnResetFilter');

        function bindCardTilt() {
            document.querySelectorAll('.animal-card').forEach(function(card){
                card.addEventListener('mousemove', function(e){
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const rotateX = ((y / rect.height) - 0.5) * -8;
                    const rotateY = ((x / rect.width) - 0.5) * 8;
                    card.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
                });
                card.addEventListener('mouseleave', function(){
                    card.style.transform = '';
                });
            });
        }

        async function fetchMarketplace(page = 1) {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            params.set('page', page);

            // Update URL browser tanpa me-refresh halaman
            const newUrl = window.location.pathname + '?' + params.toString() + '#marketplace';
            window.history.pushState({ path: newUrl }, '', newUrl);

            if (marketplaceLoader) marketplaceLoader.style.display = 'block';
            if (marketplaceGrid) marketplaceGrid.style.opacity = '0.35';

            try {
                const response = await fetch('ajax_marketplace.php?' + params.toString());
                if (response.ok) {
                    const html = await response.text();
                    if (marketplaceGrid) {
                        marketplaceGrid.innerHTML = html;
                        bindCardTilt();
                        attachPaginationEvents();
                    }
                }
            } catch (err) {
                console.warn('Gagal memuat data marketplace asinkron:', err);
            } finally {
                if (marketplaceLoader) marketplaceLoader.style.display = 'none';
                if (marketplaceGrid) marketplaceGrid.style.opacity = '1';
            }
        }

        function attachPaginationEvents() {
            document.querySelectorAll('.ajax-page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetPage = this.getAttribute('data-page') || 1;
                    fetchMarketplace(targetPage);
                    const marketElem = document.getElementById('marketplace');
                    if (marketElem) {
                        marketElem.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        }

        // Live filter listener untuk select selain dropdown hewan
        document.querySelectorAll('.live-filter').forEach(select => {
            if (select.id !== 'hewan') {
                select.addEventListener('change', function() {
                    fetchMarketplace(1);
                });
            }
        });

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchMarketplace(1);
            });
        }

        if (btnResetFilter) {
            btnResetFilter.addEventListener('click', function() {
                filterForm.reset();
                document.getElementById('ras').innerHTML = "<option value=''>Pilih Ras</option>";
                fetchMarketplace(1);
            });
        }

        // Inisialisasi awal
        bindCardTilt();
        attachPaginationEvents();

        // Registrasi Service Worker PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('sw.js')
                    .then(function(registration) {
                        console.log('TernakApp Service Worker registered with scope:', registration.scope);
                    })
                    .catch(function(error) {
                        console.warn('TernakApp Service Worker registration failed:', error);
                    });
            });
        }

        // Penanganan Tombol Install PWA (A2HS)
        let deferredPrompt;
        const pwaInstallNav = document.getElementById('pwaInstallNav');
        const pwaInstallBtn = document.getElementById('pwaInstallBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (pwaInstallNav) {
                pwaInstallNav.style.display = 'block';
            }
        });

        if (pwaInstallBtn) {
            pwaInstallBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        console.log('Pengguna menyetujui instalasi PWA TernakApp');
                    }
                    deferredPrompt = null;
                    pwaInstallNav.style.display = 'none';
                }
            });
        }

        window.addEventListener('appinstalled', () => {
            console.log('TernakApp PWA berhasil di-install');
            if (pwaInstallNav) {
                pwaInstallNav.style.display = 'none';
            }
        });
    </script>

</body>

</html>