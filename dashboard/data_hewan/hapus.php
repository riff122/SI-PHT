<?php

session_start();

include '../../config/koneksi.php';


// cek login
if(!isset($_SESSION['user'])){
    header("Location: ../../home.php");
    exit;
}


// cek role admin
if($_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}



if(isset($_GET['id'])){


    $id = $_GET['id'];



    // cek data hewan
    $cek = mysqli_query($conn,"
        SELECT * FROM hewan 
        WHERE id='$id'
    ");


    $data = mysqli_fetch_assoc($cek);



    if(!$data){

        echo "
        <script>
        alert('Data hewan tidak ditemukan!');
        window.location='index.php';
        </script>
        ";

        exit;

    }



    // hanya boleh hapus jika sudah terjual
    if($data['status'] != 'Terjual'){


        echo "
        <script>
        alert('Hanya hewan yang sudah terjual yang boleh dihapus!');
        window.location='index.php';
        </script>
        ";


        exit;

    }



    // hapus foto jika ada
    if(!empty($data['foto'])){


        $file = "../../images/hewan/".$data['foto'];


        if(file_exists($file)){

            unlink($file);

        }

    }



    // hapus data
    $hapus = mysqli_query($conn,"
        DELETE FROM hewan 
        WHERE id='$id'
    ");



    if($hapus){


        echo "
        <script>
        alert('Postingan hewan berhasil dihapus!');
        window.location='index.php';
        </script>
        ";


    }else{


        echo "
        <script>
        alert('Gagal menghapus data!');
        window.location='index.php';
        </script>
        ";

    }



}else{


    header("Location:index.php");

}


?>