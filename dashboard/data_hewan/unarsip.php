<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../../home.php");
    exit;
}

$id = $_GET['id'] ?? '';

if ($id == '') {
    header("Location: arsip/index.php");
    exit;
}

$stmt = mysqli_prepare($conn, "
    UPDATE hewan
    SET status_arsip = 0
    WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    header("Location: arsip/index.php?pesan=dipulihkan");
    exit;
} else {

    echo "Gagal memulihkan data.";
}
