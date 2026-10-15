<?php

session_start();
include '../../config/koneksi.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}


$id = $_GET['id'];


// cegah hapus akun admin sendiri
if($id == $_SESSION['user']['id']){
    echo "
    <script>
    alert('Akun admin sendiri tidak bisa dihapus!');
    window.location='index.php';
    </script>";
    exit;
}


$query = mysqli_query($koneksi, 
"DELETE FROM user WHERE id='$id'"
);


if($query){

echo "
<script>
alert('Akun berhasil dihapus');
window.location='index.php';
</script>";

}else{

echo "
<script>
alert('Gagal menghapus akun');
window.location='index.php';
</script>";

}

?>