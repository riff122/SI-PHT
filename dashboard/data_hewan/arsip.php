<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../../home.php");
    exit;
}

if($_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}

$id = $_GET['id'] ?? '';

if($id == ''){
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Hanya hewan TERJUAL yang boleh diarsipkan
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare($conn, "
    UPDATE hewan
    SET status_arsip = 1
    WHERE id = ?
    AND status = 'Terjual'
");

mysqli_stmt_bind_param($stmt, "i", $id);

if(mysqli_stmt_execute($stmt)){

    header("Location: index.php?pesan=arsip");
    exit;

}else{

    echo "Gagal mengarsipkan data.";

}

?>