<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/koneksi.php';

if(isset($_POST['login'])){

    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn,
    "SELECT * FROM users WHERE email='$email'");

    // cek email
    if(mysqli_num_rows($query) > 0){

        $data = mysqli_fetch_assoc($query);

        // cek password
        if(password_verify($password, $data['password'])){


    // cek akun tidak aktif otomatis 6 bulan

    if($data['last_login'] != null){

        $cek_tanggal = strtotime($data['last_login']);
        $sekarang = time();

        $selisih = $sekarang - $cek_tanggal;


        // 6 bulan (180 hari)
        if($selisih > (180 * 24 * 60 * 60)){


            mysqli_query($conn,
            "UPDATE users SET status='tidak_aktif'
            WHERE id='$data[id]'");


            echo "
            <script>
            alert('Akun tidak aktif karena lama tidak digunakan. Silahkan hubungi admin.');
            window.location='../home.php';
            </script>";

            exit;

        }

    }



    // cek status akun

    if($data['status']=='tidak_aktif'){


        echo "
        <script>
        alert('Akun tidak aktif. Silahkan hubungi admin.');
        window.location='../home.php';
        </script>";


        exit;

    }

        mysqli_query($conn,

        "UPDATE users 
            SET last_login=NOW()
            WHERE id='$data[id]'"

);


            $_SESSION['user'] = [
    'id' => $data['id'],
    'nama' => $data['nama'],
    'role' => $data['role']
];
            

            // redirect role
            if($data['role'] == 'admin'){

                header("Location: ../dashboard/admin.php");

            }else{

                header("Location: ../dashboard/user.php");
            }

            exit;

            }else{

            echo "
            <script>
                alert('Password salah!');
                window.location='../home.php';
            </script>
            ";

            exit;
        }

    }else{

        echo "
        <script>
            alert('Email tidak ditemukan!');
            window.location='../home.php';
        </script>
        ";

        exit;
    }

}else{

    echo "Tombol login tidak terbaca";

}
?>