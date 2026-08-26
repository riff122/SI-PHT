<?php

session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$query = mysqli_query($conn,
"SELECT * FROM users WHERE id='$user_id'");

$user = mysqli_fetch_assoc($query);

if(isset($_POST['simpan'])){

    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $wa      = mysqli_real_escape_string($conn, $_POST['wa']);
    $alamat  = mysqli_real_escape_string($conn, $_POST['alamat']);

    // ==== BAGIAN BARU: proses upload foto ====
    $fotoQuery = ""; // untuk ditambahkan ke SQL UPDATE kalau ada foto baru

    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === 0){

        $ekstensiOk = ['jpg','jpeg','png','webp'];
        $namaAsli   = $_FILES['foto']['name'];
        $ekstensi   = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

        if(in_array($ekstensi, $ekstensiOk)){

            // Nama file unik: id user + waktu, biar tiap orang beda & tidak bentrok
            $namaFile = 'user_' . $user_id . '_' . time() . '.' . $ekstensi;

            $folderTujuan = '../images/profil/';
            $tujuanFull   = $folderTujuan . $namaFile;

            // Pastikan folder tujuan ada
            if(!is_dir($folderTujuan)){
                mkdir($folderTujuan, 0755, true);
            }

            if(move_uploaded_file($_FILES['foto']['tmp_name'], $tujuanFull)){

                // Hapus foto lama supaya folder tidak menumpuk file
                if(!empty($user['foto']) && file_exists($folderTujuan . $user['foto'])){
                    unlink($folderTujuan . $user['foto']);
                }

                $namaFileEscaped = mysqli_real_escape_string($conn, $namaFile);
                $fotoQuery = ", foto='$namaFileEscaped'";
            }
        }
    }
    // ==== AKHIR BAGIAN BARU ====

    mysqli_query($conn,"
        UPDATE users
        SET
            nama='$nama',
            wa='$wa',
            alamat='$alamat'
            $fotoQuery
        WHERE id='$user_id'
    ");

    $_SESSION['user']['nama'] = $nama;

    echo "<script>
        alert('Profil berhasil diperbarui');
        window.location='profil.php';
    </script>";
    exit;
}

// ==== BAGIAN BARU: tentukan foto yang mau ditampilkan ====
$folderFoto = '../images/profil/';
if(!empty($user['foto']) && file_exists($folderFoto . $user['foto'])){
    $tampilFoto = $folderFoto . $user['foto'];
} else {
    // foto default kalau user belum pernah upload
    $tampilFoto = '../images/profil/default.png';
}
// ==== AKHIR BAGIAN BARU ====

?>

<!DOCTYPE html>
<html>
<head>

<title>Profil Saya</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<body>

<div class="container py-5">

<!-- Tombol Kembali ke Dashboard -->
<div class="mb-3">
    <a href="user.php" class="btn btn-outline-success">
        ← 
    </a>
</div>

<h2 class="mb-4">
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
Profil Saya
</h2>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">

<label>Nama</label>

<input
type="text"
name="nama"
class="form-control"
value="<?= $user['nama'];?>"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
class="form-control"
value="<?= $user['email'];?>"
readonly>

</div>

<div class="mb-3">

<label>WhatsApp</label>

<input
type="text"
name="wa"
class="form-control"
value="<?= $user['wa'];?>"
required>

</div>

<div class="mb-3">

<label>Alamat</label>

<textarea
name="alamat"
class="form-control"
required><?= $user['alamat'];?></textarea>

</div>

<div class="mb-3">

<label>Foto Profil</label>

<input
type="file"
name="foto"
class="form-control"
accept="image/*">

</div>

<button
type="submit"
name="simpan"
class="btn btn-success">

Simpan Perubahan

</button>

</form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>