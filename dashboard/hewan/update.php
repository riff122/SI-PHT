<?php

session_start();
include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$id = (int)$_GET['id'];

if(isset($_POST['update'])){

    $nama = mysqli_real_escape_string($conn, $_POST['nama_hewan']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);

    // BERAT
    $berat = $_POST['berat'];
    $satuan_berat = mysqli_real_escape_string(
        $conn,
        $_POST['satuan_berat']
    );

    // UMUR
    $umur = mysqli_real_escape_string($conn, $_POST['umur']);

    // HARGA
    // Hilangkan titik sebelum disimpan ke database
    $harga = str_replace('.', '', $_POST['harga']);
    $harga = (int)$harga;

    // DESKRIPSI
    $deskripsi = mysqli_real_escape_string(
        $conn,
        $_POST['deskripsi']
    );

    // STATUS
    $status = mysqli_real_escape_string(
        $conn,
        $_POST['status']
    );


    /* ==========================================
       TANGGAL TERJUAL
    ========================================== */

    if($status == 'Terjual'){

        $tanggal_terjual = "NOW()";

    }else{

        $tanggal_terjual = "NULL";

    }


    /* ==========================================
       UPDATE DATA HEWAN
    ========================================== */

    $update = mysqli_query($conn,"
        UPDATE hewan SET

            nama_hewan='$nama',

            jenis='$jenis',

            berat='$berat',

            satuan_berat='$satuan_berat',

            umur='$umur',

            harga='$harga',

            deskripsi='$deskripsi',

            status='$status',

            tanggal_terjual=$tanggal_terjual

        WHERE id='$id'

        AND user_id='$user_id'
    ");


    /* ==========================================
       HASIL UPDATE
    ========================================== */

    if($update){

        echo "
        <script>

            alert('Data berhasil diupdate');

            window.location='index.php';

        </script>
        ";

        exit;

    }else{

        echo "
        <script>

            alert('Gagal mengupdate data');

            history.back();

        </script>
        ";

        exit;

    }

}else{

    header("Location:index.php");
    exit;

}

?>