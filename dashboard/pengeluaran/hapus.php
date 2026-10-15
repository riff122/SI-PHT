<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){

    header("Location: ../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$id = $_GET['id'];

$query = mysqli_query($conn,
"SELECT * FROM pengeluaran
WHERE id='$id'
AND user_id='$user_id'");

$data = mysqli_fetch_assoc($query);

if(!$data){

    echo "
    <script>
        alert('Data tidak ditemukan');
        window.location='index.php';
    </script>
    ";

    exit;
}

$hapus = mysqli_query($conn,
"DELETE FROM pengeluaran
WHERE id='$id'");

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