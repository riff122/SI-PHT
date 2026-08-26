<?php

include '../config/koneksi.php';

if(isset($_POST['register'])){

$nama  = $_POST['nama'];
$email = $_POST['email'];
$wa    = $_POST['wa'];

$desa       = $_POST['desa'];
$kelurahan  = $_POST['kelurahan'];
$kecamatan  = $_POST['kecamatan'];
$kabupaten  = $_POST['kabupaten'];

$alamat = $desa.", ".$kelurahan.", ".$kecamatan.", ".$kabupaten;

    

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $cek = mysqli_query($conn,
    "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($cek) > 0){

        echo "
        <script>
            alert('Email sudah digunakan!');
            window.location='../home.php';
        </script>
        ";

    }else{

        mysqli_query($conn,"
        INSERT INTO users
            (nama,email,wa,alamat,desa,password,role)
        VALUES
            ('$nama','$email','$wa','$alamat','$desa','$password','peternak')");

        echo "
        <script>
            alert('Registrasi berhasil!');
            window.location='../home.php';
        </script>
        ";
    }

}
?>