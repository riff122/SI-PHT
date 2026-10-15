<?php
session_start();
include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../../home.php");
    exit;
}

if($_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}

$id = (int)$_GET['id'];

$query = mysqli_query($conn,"
SELECT
    hewan.*,
    users.nama AS peternak,
    users.email,
    users.wa,
    users.alamat
FROM hewan
JOIN users
ON hewan.user_id = users.id
WHERE hewan.id='$id'
");

$data = mysqli_fetch_assoc($query);

if(!$data){
    echo "Data tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<title>Detail Hewan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../../style.css">

</head>

<body class="dashboard-admin">

<div class="container mt-5">

<div class="card shadow-lg">

<div class="card-header bg-dark text-white">

<h3>Detail Hewan</h3>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 text-center">

<?php if(!empty($data['foto'])){ ?>

<img src="../../images/hewan/<?= htmlspecialchars($data['foto']); ?>"
class="img-fluid rounded shadow"
style="max-height:350px;object-fit:cover;">

<?php } ?>

</div>

<div class="col-md-8">

<table class="table">

<tr>
<th width="220">Nama Hewan</th>
<td><?= htmlspecialchars($data['nama_hewan']); ?></td>
</tr>

<tr>
<th>Jenis</th>
<td><?= htmlspecialchars($data['jenis']); ?></td>
</tr>

<tr>
<th>Berat</th>
<td><?= htmlspecialchars($data['berat']); ?> Kg</td>
</tr>

<tr>
<th>Umur</th>
<td><?= htmlspecialchars($data['umur']); ?></td>
</tr>

<tr>
<th>Harga</th>
<td>Rp <?= number_format($data['harga'],0,',','.'); ?></td>
</tr>

<tr>
<th>Status</th>
<td><?= htmlspecialchars($data['status']); ?></td>
</tr>

<tr>
<th>Peternak</th>
<td><?= htmlspecialchars($data['peternak']); ?></td>
</tr>

<tr>
<th>Email</th>
<td><?= htmlspecialchars($data['email']); ?></td>
</tr>

<tr>
<th>No WhatsApp</th>
<td><?= htmlspecialchars($data['wa']); ?></td>
</tr>

<tr>
<th>Alamat</th>
<td><?= htmlspecialchars($data['alamat']); ?></td>
</tr>

<tr>
<th>Deskripsi</th>
<td><?= nl2br(htmlspecialchars($data['deskripsi'])); ?></td>
</tr>

</table>

<a href="index.php" class="btn btn-secondary">
Kembali
</a>


</div>

</div>

</div>

</div>

</div>

</body>
</html>