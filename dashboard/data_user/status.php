<?php

session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../home.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$aksi = $_GET['aksi'] ?? '';

// cegah admin menonaktifkan dirinya sendiri
if ($id === (int)$_SESSION['user']['id']) {
    echo "<script>
        alert('Akun admin yang sedang login tidak bisa dinonaktifkan!');
        window.location='index.php';
    </script>";
    exit;
}

$status = ($aksi === "nonaktif") ? "tidak_aktif" : "aktif";

$stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);
$query = mysqli_stmt_execute($stmt);

if ($query) {
    echo "<script>
        alert('Status akun berhasil diperbarui');
        window.location='index.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal memperbarui status akun: " . addslashes(mysqli_error($conn)) . "');
        window.location='index.php';
    </script>";
}
