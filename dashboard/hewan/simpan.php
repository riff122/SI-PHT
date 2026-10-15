<?php

session_start();

include '../../config/koneksi.php';

$user_id = $_SESSION['id'];

$nama_hewan = $_POST['nama_hewan'];
$jenis = $_POST['jenis'];
$berat = $_POST['berat'];
$umur = $_POST['umur'];
$harga = $_POST['harga'];
$lokasi = $_POST['lokasi'];
$deskripsi = $_POST['deskripsi'];


// FOTO
$foto = $_FILES['foto']['name'];

$tmp = $_FILES['foto']['tmp_name'];


// GENERATE NAMA RANDOM
$nama_foto = time() . '_' . $foto;


// UPLOAD
move_uploaded_file(
    $tmp,
    '../../assets/images/' . $nama_foto
);


// SIMPAN DATABASE
mysqli_query($conn,
"INSERT INTO hewan(

user_id,
nama_hewan,
jenis,
berat,
umur,
harga,
lokasi,
foto,
deskripsi

)

VALUES(

'$user_id',
'$nama_hewan',
'$jenis',
'$berat',
'$umur',
'$harga',
'$lokasi',
'$nama_foto',
'$deskripsi'

)");

header("Location: index.php");