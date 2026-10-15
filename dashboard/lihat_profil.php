<?php

session_start();
include '../config/koneksi.php';
// id peternak yang mau dilihat, dikirim lewat URL: lihat_profil.php?id=5
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: ../home.php");
    exit;
}
$id_dilihat = (int) $_GET['id'];
$id_hewan_asal = isset($_GET['balik']) && is_numeric($_GET['balik']) ? (int) $_GET['balik'] : null;
$link_kembali = '../detail.php' . ($id_hewan_asal ? '?id=' . $id_hewan_asal : '');
// Kalau user yang login lagi lihat profilnya sendiri, arahkan ke halaman edit aslinya
if(isset($_SESSION['user']) && $id_dilihat === (int) $_SESSION['user']['id']){
    header("Location: profil.php");
    exit;
}
$stmt = mysqli_prepare($conn, "SELECT nama, wa, alamat, foto FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_dilihat);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
if(!$user){
    header("Location: ../home.php");
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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Peternak</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body{
        font-size: 20px;
    }
    h2{
        font-size: 2rem;
    }
    .text-muted.small{
        font-size: 1rem !important;
    }
    .btn{
        font-size: 1.25rem;
        padding: 14px;
    }
    .card-body > div{
        margin-bottom: 20px !important;
    }
</style>
</head>
<body>
<div class="container py-5" style="max-width:600px;">
<a href="<?= $link_kembali; ?>" class="btn btn-outline-secondary btn-sm mb-4">&larr; Kembali</a>
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
<h2 class="mb-4 text-center"><?= htmlspecialchars($user['nama']); ?></h2>
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