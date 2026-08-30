<?php

session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_POST['update'])) {

    $nama = trim($_POST['nama_hewan'] ?? '');
    $jenis = trim($_POST['jenis'] ?? '');
    $berat = (float)str_replace(',', '.', $_POST['berat'] ?? '0');
    $satuan_berat = trim($_POST['satuan_berat'] ?? 'kg');
    $umur = trim($_POST['umur'] ?? '');
    $harga = (int)str_replace('.', '', $_POST['harga'] ?? '0');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = trim($_POST['status'] ?? 'Tersedia');

    if ($status === 'Terjual') {
        $stmt = mysqli_prepare($conn, "
            UPDATE hewan SET
                nama_hewan = ?,
                jenis = ?,
                berat = ?,
                satuan_berat = ?,
                umur = ?,
                harga = ?,
                deskripsi = ?,
                status = ?,
                tanggal_terjual = COALESCE(tanggal_terjual, NOW()),
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        mysqli_stmt_bind_param(
            $stmt,
            "ssdssissii",
            $nama,
            $jenis,
            $berat,
            $satuan_berat,
            $umur,
            $harga,
            $deskripsi,
            $status,
            $id,
            $user_id
        );
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE hewan SET
                nama_hewan = ?,
                jenis = ?,
                berat = ?,
                satuan_berat = ?,
                umur = ?,
                harga = ?,
                deskripsi = ?,
                status = ?,
                tanggal_terjual = NULL,
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        mysqli_stmt_bind_param(
            $stmt,
            "ssdssissii",
            $nama,
            $jenis,
            $berat,
            $satuan_berat,
            $umur,
            $harga,
            $deskripsi,
            $status,
            $id,
            $user_id
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Data berhasil diupdate');
            window.location='index.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Gagal mengupdate data: " . addslashes(mysqli_error($conn)) . "');
            history.back();
        </script>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
