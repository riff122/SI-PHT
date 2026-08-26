<?php

session_start();
include '../../config/koneksi.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}


$id = $_GET['id'];
$aksi = $_GET['aksi'];


// cegah admin menonaktifkan dirinya sendiri
if($id == $_SESSION['user']['id']){

echo "
<script>
alert('Akun admin sendiri tidak bisa diubah!');
window.location='index.php';
</script>";

exit;

}



if($aksi == "nonaktif"){

$status = "tidak_aktif";


}else{


$status = "aktif";

}



$query = mysqli_query($conn,

"UPDATE users SET status='$status' WHERE id='$id'"

);



if($query){

echo "
<script>
alert('Status akun berhasil diperbarui');
window.location='index.php';
</script>";

}else{


echo "
<script>
alert('Gagal memperbarui status akun');
window.location='index.php';
</script>";

}


?>