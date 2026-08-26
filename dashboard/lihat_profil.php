<?php

session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
    exit;
}

// id peternak yang mau dilihat, dikirim lewat URL: lihat_profil.php?id=5
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: dashboard.php");
    exit;
}

$id_dilihat = (int) $_GET['id']; // dipaksa jadi angka, aman dari SQL injection

// id hewan yang tadi dibuka di detail.php, dititipkan biar tombol Kembali bisa balik ke hewan yang sama
$id_hewan_asal = isset($_GET['balik']) && is_numeric($_GET['balik']) ? (int) $_GET['balik'] : null;

// Susun link tombol kembali: kalau ada id hewan, balik ke detail hewan itu, kalau tidak ada balik ke detail.php polos
$link_kembali = '../detail.php' . ($id_hewan_asal ? '?id=' . $id_hewan_asal : '');

// Kalau user coba lihat profilnya sendiri lewat link ini, arahkan ke halaman edit aslinya
if($id_dilihat === (int) $_SESSION['user']['id']){
    header("Location: profil.php");
    exit;
}

// Pakai prepared statement, BUKAN concat string, supaya aman dari SQL injection
$stmt = mysqli_prepare($conn, "SELECT nama, wa, alamat, foto FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_dilihat);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Kalau id tidak ditemukan di database
if(!$user){
    header("Location: dashboard.php");
    exit;
}

$folderFoto = '../images/profil/';
if(!empty($user['foto']) && file_exists($folderFoto . $user['foto'])){
    $tampilFoto = $folderFoto . $user['foto'];
} else {
    $tampilFoto = '../images/profil/default.png';
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Profil Peternak</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container py-5" style="max-width:600px;">

<a href="<?= $link_kembali; ?>" class="btn btn-outline-secondary btn-sm mb-4">&larr; Kembali</a>

<h2 class="mb-4 text-center">
<div class="text-center mb-4">

<img
src="<?= $tampilFoto; ?>"
class="rounded-circle shadow"
width="170"
height="170"
style="object-fit:cover; cursor:pointer;"
data-bs-toggle="modal"
data-bs-target="#modalFoto">

</div>

<!-- Modal untuk menampilkan foto profil dalam ukuran besar -->
<div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body text-center p-0">
        <img
        src="<?= $tampilFoto; ?>"
        class="img-fluid rounded shadow"
        style="max-height:80vh; object-fit:contain;">
      </div>
    </div>
  </div>
</div>

<?= htmlspecialchars($user['nama']); ?>
</h2>

<!-- Ini semua cuma tampilan teks biasa, BUKAN input form, jadi tidak bisa diubah -->

<div class="card">
  <div class="card-body">

    <div class="mb-3">
      <div class="text-muted small">Alamat</div>
      <div><?= nl2br(htmlspecialchars($user['alamat'])); ?></div>
    </div>

    <div class="mb-3">
      <div class="text-muted small">WhatsApp</div>
      <div><?= htmlspecialchars($user['wa']); ?></div>
    </div>

    <a
    href="https://wa.me/62<?= ltrim(preg_replace('/[^0-9]/', '', $user['wa']), '0'); ?>"
    target="_blank"
    class="btn btn-success w-100">
    Chat via WhatsApp
    </a>

  </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>