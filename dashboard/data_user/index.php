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

// ===============================
// PAGINATION
// ===============================
$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;


// Hitung total data user
$totalQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM users
");

$totalData = mysqli_fetch_assoc($totalQuery)['total'];

$totalPage = ceil($totalData / $limit);


// Ambil data sesuai halaman
$query = mysqli_query($conn, "
    SELECT *
    FROM users
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../style.css">

</head>

<body class="dashboard-admin">

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>Data User</h2>

    <a href="../admin.php" class="btn btn-secondary">
        ← Kembali
    </a>

</div>

<div class="card shadow">

<div class="card-body">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">

<tr>

<th width="60">No</th>
<th>Nama</th>
<th>Email</th>
<th>WhatsApp</th>
<th>Role</th>
<th width="180">Aksi</th>

</tr>

<tbody>

<?php

$no = $offset + 1;

while($data = mysqli_fetch_assoc($query)){

?>

<tr>

<td><?= $no++; ?></td>

<td><?= htmlspecialchars($data['nama']); ?></td>

<td><?= htmlspecialchars($data['email']); ?></td>

<td><?= htmlspecialchars($data['wa']); ?></td>

<td>

<?php
if($data['role']=='admin'){
    echo '<span class="badge bg-danger">Admin</span>';
}else{
    echo '<span class="badge bg-success">Peternak</span>';
}
?>

</td>

<td>

<a href="detail.php?id=<?= $data['id']; ?>" 
class="btn btn-info btn-sm">
Detail
</a>

<?php if($data['status']=="aktif"){ ?>

<a href="status.php?id=<?= $data['id']; ?>&aksi=nonaktif"
class="btn btn-warning btn-sm"
onclick="return confirm('Nonaktifkan akun ini?')">
Nonaktifkan
</a>

<?php }else{ ?>

<a href="status.php?id=<?= $data['id']; ?>&aksi=aktif"
class="btn btn-success btn-sm"
onclick="return confirm('Aktifkan kembali akun ini?')">
Aktifkan
</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>
</table>

<!-- PAGINATION -->
<?php if($totalPage > 1){ ?>

<nav class="mt-4">

    <ul class="pagination justify-content-center">

        <!-- Sebelumnya -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">

            <a class="page-link"
               href="?page=<?= $page - 1; ?>">

                ← Sebelumnya

            </a>

        </li>


        <!-- Nomor halaman -->
        <?php for($i = 1; $i <= $totalPage; $i++){ ?>

            <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">

                <a class="page-link"
                   href="?page=<?= $i; ?>">

                    <?= $i; ?>

                </a>

            </li>

        <?php } ?>


        <!-- Berikutnya -->
        <li class="page-item <?= ($page >= $totalPage) ? 'disabled' : ''; ?>">

            <a class="page-link"
               href="?page=<?= $page + 1; ?>">

                Berikutnya →

            </a>

        </li>

    </ul>

</nav>

<?php } ?>

</div>

</div>

</div>

</body>
</html>