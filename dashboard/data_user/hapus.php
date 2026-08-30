<?php

session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../home.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// cegah hapus akun admin sendiri
if ($id === (int)$_SESSION['user']['id']) {
    echo "<script>
        alert('Akun admin yang sedang login tidak bisa dihapus!');
        window.location='index.php';
    </script>";
    exit;
}

// Cek data user dan hapus foto profil jika ada
$stmtCek = mysqli_prepare($conn, "SELECT foto FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmtCek, "i", $id);
mysqli_stmt_execute($stmtCek);
$resCek = mysqli_stmt_get_result($stmtCek);
$userData = mysqli_fetch_assoc($resCek);

if ($userData && !empty($userData['foto'])) {
    $fotoProfil = "../../images/profil/" . $userData['foto'];
    if (file_exists($fotoProfil)) {
        unlink($fotoProfil);
    }
}

// Hapus user dengan Prepared Statement
$stmtDel = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmtDel, "i", $id);
$query = mysqli_stmt_execute($stmtDel);

if ($query) {
    echo "<script>
        alert('Akun berhasil dihapus');
        window.location='index.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus akun: " . addslashes(mysqli_error($conn)) . "');
        window.location='index.php';
    </script>";
}
