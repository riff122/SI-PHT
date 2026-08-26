<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header('Location: ../../home.php');
    exit;
}

if($_SESSION['user']['role'] != 'admin'){
    header('Location: ../home.php');
    exit;
}

// =======================
// STATISTIK DASHBOARD
// =======================

// Total User
$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$total_user = mysqli_fetch_assoc($q);

// Total Peternak
$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='peternak'");
$total_peternak = mysqli_fetch_assoc($q);

// Total Hewan
$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hewan");
$total_hewan = mysqli_fetch_assoc($q);

// Marketplace
$status = mysqli_query($conn, "SHOW COLUMNS FROM hewan LIKE 'status'");

if(mysqli_num_rows($status) > 0){

    $q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hewan WHERE status='Tersedia'");
    $total_marketplace = mysqli_fetch_assoc($q);

}else{

    $q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hewan");
    $total_marketplace = mysqli_fetch_assoc($q);

}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../style.css">

</head>

<body class="dashboard-admin">

<div class="sidebar">

    <h3>🐄 KurbanApp</h3>

    <hr>

<a href="admin.php">Dashboard</a>

<a href="data_user/index.php">Data User</a>

<a href="data_hewan/index.php">Data Hewan</a>

<a href="../#marketplace">Marketplace</a>

<a href="logout.php">Logout</a>

</div>

<div class="content">

<h2>Selamat Datang, <?= $_SESSION['user']['nama']; ?></h2>

<p>Dashboard Administrator</p>

<div class="row g-4 mt-4">

<div class="col-md-3">
<div class="stats-card p-4">
<h5>Total User</h5>
<h1><?= $total_user['total']; ?></h1>
</div>
</div>

<div class="col-md-3">
<div class="stats-card p-4">
<h5>Total Peternak</h5>
<h1><?= $total_peternak['total']; ?></h1>
</div>
</div>

<div class="col-md-3">
<div class="stats-card p-4">
<h5>Total Hewan</h5>
<h1><?= $total_hewan['total']; ?></h1>
</div>
</div>

<div class="col-md-3">
<div class="stats-card p-4">
<h5>Marketplace</h5>
<h1><?= $total_marketplace['total']; ?></h1>
</div>
</div>

</div>

</div>

</body>
</html>