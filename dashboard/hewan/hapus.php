<?php

session_start();

include '../../config/koneksi.php';

// cek login
if(!isset($_SESSION['user'])){

    header("Location: ../../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ambil id hewan
$id = (int)$_GET['id'];

// cek apakah hewan milik user login
$query = mysqli_query($conn,
"SELECT * FROM hewan
WHERE id='$id'
AND user_id='$user_id'");

$data = mysqli_fetch_assoc($query);

// jika data tidak ada
if(!$data){

    echo "
    <script>
        alert('Data tidak ditemukan');
        window.location='index.php';
    </script>
    ";

    exit;
}

// hapus gambar jika ada
$path = "../../assets/images/".$data['foto'];

if(file_exists($path)){

    unlink($path);

}

// hapus database
$hapus = mysqli_query($conn,
"DELETE FROM hewan
WHERE id='$id'");

// cek hasil
if($hapus){

    echo "
    <script>
        alert('Data berhasil dihapus');
        window.location='index.php';
    </script>
    ";

}else{

    echo "
    <script>
        alert('Gagal menghapus data');
        window.location='index.php';
    </script>
    ";
}

?>