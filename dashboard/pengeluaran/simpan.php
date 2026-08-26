<?php

session_start();

include '../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$hewan_id = $_POST['hewan_id'];
$nama = $_POST['nama_pengeluaran'];
$nominal = $_POST['nominal'];
$keterangan = $_POST['keterangan'];
$tanggal = $_POST['tanggal'];

mysqli_query($conn,
"INSERT INTO pengeluaran(
    hewan_id,
    user_id,
    nama_pengeluaran,
    jumlah,
    tanggal,
    keterangan
)

VALUES(
    '$hewan_id',
    '$user_id',
    '$nama',
    '$nominal',
    '$tanggal',
    '$keterangan'
)");

header("Location: index.php");

?>