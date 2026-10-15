<?php

session_start();
include '../../config/koneksi.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}

$id = (int)$_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM users WHERE id='$id'
"));

if(!$data){
    echo "Data akun tidak ditemukan.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<title>Detail Akun</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../../style.css">

<style>

.detail-wrapper{
    max-width: 900px;
    margin: 50px auto;
}

.detail-card{
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #cfcfcf;
    background: #fff;
}

.detail-header{
    background: #212529;
    color: #fff;
    padding: 20px 25px;
}

.detail-header h3{
    margin: 0;
    font-family: Georgia, serif;
    font-size: 30px;
}

.detail-header p{
    margin: 5px 0 0;
    color: #cfcfcf;
    font-size: 14px;
}

.detail-body{
    padding: 30px;
}

.profile-box{
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px;
    margin-bottom: 25px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e1e1e1;
}

.profile-icon{
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: #212529;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
}

.profile-name{
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 3px;
    color: #1b1818;
}

.profile-email{
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 3px;
    color: #696363
}

.detail-table{
    width: 100%;
    border-collapse: collapse;
}

.detail-table tr{
    border-bottom: 1px solid #dee2e6;
}

.detail-table tr:last-child{
    border-bottom: none;
}

.detail-table th{
    width: 220px;
    padding: 17px 10px;
    font-weight: 600;
    color: #212529;
}

.detail-table td{
    padding: 17px 10px;
    color: #333;
}

.detail-footer{
    margin-top: 25px;
}

.btn-kembali{
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 7px;
    text-decoration: none;
}

.btn-kembali:hover{
    background: #5c636a;
    color: white;
}

</style>

</head>

<body class="dashboard-admin">

<div class="container">

    <div class="detail-wrapper">

        <div class="detail-card shadow-lg">

            <!-- HEADER -->
            <div class="detail-header">

                <h3>Detail Akun</h3>

                <p>Informasi lengkap akun pengguna</p>

            </div>


            <!-- BODY -->
            <div class="detail-body">

                <!-- PROFIL SINGKAT -->
                <div class="profile-box">

    <div class="profile-icon">
        <?= strtoupper(substr($data['nama'], 0, 1)); ?>
    </div>

    <div>

        <div class="profile-name">
            <?= htmlspecialchars($data['nama']); ?>
        </div>

        <div class="profile-email">
            <?= htmlspecialchars($data['email']); ?>
        </div>

    </div>

</div>


                <!-- DETAIL -->
                <table class="detail-table">

                    <tr>
                        <th>Nama Lengkap</th>
                        <td>
                            <?= htmlspecialchars($data['nama']); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Alamat</th>
                        <td>
                            <?= htmlspecialchars($data['alamat']); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>No Whatshapp</th>
                        <td>
                            <?= htmlspecialchars($data['wa']); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Role</th>
                        <td>

                            <?php if($data['role'] == 'admin'){ ?>

                                <span class="badge bg-dark px-3 py-2">
                                    Administrator
                                </span>

                            <?php } else { ?>

                                <span class="badge bg-secondary px-3 py-2">
                                    Peternak
                                </span>

                            <?php } ?>

                        </td>
                    </tr>

                    <tr>
                        <th>Status Akun</th>

                        <td>

                            <?php if($data['status']=="aktif"){ ?>

                                <span class="badge bg-success px-3 py-2">
                                    Aktif
                                </span>

                            <?php }else{ ?>

                                <span class="badge bg-danger px-3 py-2">
                                    Tidak Aktif
                                </span>

                            <?php } ?>

                        </td>

                    </tr>

                </table>


                <!-- FOOTER -->
                <div class="detail-footer">

                    <a href="index.php" class="btn-kembali">
                        Kembali
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>