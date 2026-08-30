<?php

session_start();
include '../../config/koneksi.php';

// cek login & role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../home.php");
    exit;
}

if (isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    // cek data hewan
    $stmtCek = mysqli_prepare($conn, "SELECT * FROM hewan WHERE id = ?");
    mysqli_stmt_bind_param($stmtCek, "i", $id);
    mysqli_stmt_execute($stmtCek);
    $resCek = mysqli_stmt_get_result($stmtCek);
    $data = mysqli_fetch_assoc($resCek);

    if (!$data) {
        echo "<script>
            alert('Data hewan tidak ditemukan!');
            window.location='index.php';
        </script>";
        exit;
    }

    // hanya boleh hapus jika sudah terjual
    if ($data['status'] != 'Terjual') {
        echo "<script>
            alert('Hanya hewan yang sudah terjual yang boleh dihapus!');
            window.location='index.php';
        </script>";
        exit;
    }

    // 1. Hapus foto utama jika ada
    if (!empty($data['foto'])) {
        $file = "../../images/hewan/" . $data['foto'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // 2. Ambil dan hapus semua foto galeri pendukung
    $stmtFotos = mysqli_prepare($conn, "SELECT foto FROM hewan_foto WHERE hewan_id = ?");
    mysqli_stmt_bind_param($stmtFotos, "i", $id);
    mysqli_stmt_execute($stmtFotos);
    $resFotos = mysqli_stmt_get_result($stmtFotos);

    while ($f = mysqli_fetch_assoc($resFotos)) {
        if (!empty($f['foto'])) {
            $pathGaleri = "../../images/hewan/" . $f['foto'];
            if (file_exists($pathGaleri)) {
                unlink($pathGaleri);
            }
        }
    }

    // 3. Hapus relasi di database
    $stmtDelFoto = mysqli_prepare($conn, "DELETE FROM hewan_foto WHERE hewan_id = ?");
    mysqli_stmt_bind_param($stmtDelFoto, "i", $id);
    mysqli_stmt_execute($stmtDelFoto);

    $stmtDelPengeluaran = mysqli_prepare($conn, "DELETE FROM pengeluaran WHERE hewan_id = ?");
    mysqli_stmt_bind_param($stmtDelPengeluaran, "i", $id);
    mysqli_stmt_execute($stmtDelPengeluaran);

    // 4. Hapus postingan hewan
    $stmtDel = mysqli_prepare($conn, "DELETE FROM hewan WHERE id = ?");
    mysqli_stmt_bind_param($stmtDel, "i", $id);
    $hapus = mysqli_stmt_execute($stmtDel);

    if ($hapus) {
        echo "<script>
            alert('Postingan hewan berhasil dihapus!');
            window.location='index.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data: " . addslashes(mysqli_error($conn)) . "');
            window.location='index.php';
        </script>";
    }
} else {
    header("Location: index.php");
    exit;
}
