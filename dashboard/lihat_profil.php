<?php

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../home.php");
    exit;
}

// ID peternak yang ingin dilihat
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$id_dilihat = (int)$_GET['id'];

// ID hewan asal jika dibuka dari detail.php
$id_hewan_asal = isset($_GET['balik']) && is_numeric($_GET['balik']) ? (int)$_GET['balik'] : null;
$link_kembali = '../detail.php' . ($id_hewan_asal ? '?id=' . $id_hewan_asal : '');

// Jika user melihat profilnya sendiri, arahkan ke edit profil
if ($id_dilihat === (int)$_SESSION['user']['id']) {
    header("Location: profil.php");
    exit;
}

// Prepared statement untuk mengambil data profil peternak
$stmt = mysqli_prepare($conn, "SELECT nama, wa, alamat, foto FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_dilihat);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: user.php");
    exit;
}

$folderFoto = '../images/profil/';
if (!empty($user['foto']) && file_exists($folderFoto . $user['foto'])) {
    $tampilFoto = $folderFoto . htmlspecialchars($user['foto']);
} else {
    $tampilFoto = '../images/profil/default.png';
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Peternak - <?= htmlspecialchars($user['nama']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../style.css">
</head>

<body class="bg-light">

    <div class="container py-5" style="max-width: 600px;">

        <!-- Tombol Kembali -->
        <div class="mb-4">
            <a href="<?= $link_kembali; ?>" class="btn btn-outline-secondary btn-sm">
                ← Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm p-4 text-center">

            <!-- Foto Profil -->
            <div class="mb-3">
                <img src="<?= $tampilFoto; ?>"
                    class="rounded-circle shadow-sm"
                    width="140"
                    height="140"
                    style="object-fit: cover; cursor: pointer;"
                    data-bs-toggle="modal"
                    data-bs-target="#modalFoto"
                    alt="Foto Peternak">
            </div>

            <h3 class="fw-bold mb-1">
                <?= htmlspecialchars($user['nama']); ?>
            </h3>
            <p class="text-muted small mb-4">Peternak Terverifikasi</p>

            <div class="text-start bg-light p-3 rounded-3 mb-4">
                <div class="mb-3">
                    <div class="text-muted small fw-semibold">Alamat / Lokasi Kandang</div>
                    <div class="text-dark"><?= nl2br(htmlspecialchars($user['alamat'])); ?></div>
                </div>

                <div>
                    <div class="text-muted small fw-semibold">Nomor WhatsApp</div>
                    <div class="text-dark"><?= htmlspecialchars($user['wa']); ?></div>
                </div>
            </div>

            <a href="https://wa.me/62<?= ltrim(preg_replace('/[^0-9]/', '', $user['wa']), '0'); ?>"
                target="_blank"
                class="btn btn-success w-100 py-2 fw-semibold">
                <i class="bi bi-whatsapp me-2"></i> Chat via WhatsApp
            </a>

        </div>

    </div>

    <!-- Modal Zoom Foto Profil -->
    <div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    <img src="<?= $tampilFoto; ?>"
                        class="img-fluid rounded shadow-lg"
                        style="max-height: 80vh; object-fit: contain;"
                        alt="Zoom Foto">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>