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


// ========================================
// FILTER
// ========================================

$where = [];


// Filter nama peternak
if(isset($_GET['peternak']) && $_GET['peternak'] != ""){

    $peternak = mysqli_real_escape_string($conn, $_GET['peternak']);

    $where[] = "users.nama='$peternak'";
}


// Filter status
if(isset($_GET['status']) && $_GET['status'] != ""){

    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $where[] = "hewan.status='$status'";
}


// ========================================
// QUERY DATA
// ========================================

$sql = "
SELECT
    hewan.*,
    users.nama AS peternak
FROM hewan
JOIN users ON hewan.user_id = users.id
WHERE hewan.status_arsip = 0
";


if(count($where) > 0){
    $sql .= " AND " . implode(" AND ", $where);
}


// ========================================
// URUTAN DATA
// ========================================

if(isset($_GET['urutan'])){

    if($_GET['urutan'] == "terbaru"){

        $sql .= " ORDER BY hewan.id DESC";

    }elseif($_GET['urutan'] == "terlama"){

        $sql .= " ORDER BY hewan.id ASC";

    }else{

        $sql .= " ORDER BY hewan.id DESC";

    }

}else{

    $sql .= " ORDER BY hewan.id DESC";

}


// ========================================
// PAGINATION
// ========================================

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;


// ========================================
// HITUNG TOTAL DATA
// ========================================

$count_sql = "
SELECT COUNT(*) AS total
FROM hewan
JOIN users ON hewan.user_id = users.id
WHERE hewan.status_arsip = 0
";


if(count($where) > 0){
    $count_sql .= " AND " . implode(" AND ", $where);
}


$count_query = mysqli_query($conn, $count_sql);

$total_data = mysqli_fetch_assoc($count_query)['total'];

$total_page = ceil($total_data / $limit);


// ========================================
// LIMIT DATA
// ========================================

$sql .= " LIMIT $limit OFFSET $offset";

$query = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Hewan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../../style.css">

</head>


<body class="dashboard-admin">


<div class="container mt-5">


<!-- ========================================
     HEADER
======================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>Data Hewan</h2>


<div class="d-flex gap-2">

<a href="arsip/index.php" class="btn btn-outline-warning">

<i class="bi bi-archive"></i>

Arsip Hewan

</a>


<a href="../admin.php" class="btn btn-secondary">

← Kembali

</a>

</div>

</div>



<!-- ========================================
     CARD
======================================== -->

<div class="card shadow">


<!-- ========================================
     FILTER
======================================== -->

<div class="card-header">

<form method="GET" class="d-flex gap-2">


<!-- PETernak -->

<select name="peternak" class="form-select form-select-sm">

<option value="">
Peternak
</option>


<?php

$list_peternak = mysqli_query($conn, "

SELECT DISTINCT users.nama

FROM users

JOIN hewan
ON users.id = hewan.user_id

WHERE users.role='peternak'

ORDER BY users.nama ASC

");


while($p = mysqli_fetch_assoc($list_peternak)){

?>

<option
value="<?= htmlspecialchars($p['nama']); ?>"
<?= (isset($_GET['peternak']) && $_GET['peternak'] == $p['nama']) ? 'selected' : ''; ?>
>

<?= htmlspecialchars($p['nama']); ?>

</option>

<?php } ?>

</select>



<!-- STATUS -->

<select name="status" class="form-select form-select-sm">

<option value="">
Status
</option>


<option
value="Tersedia"
<?= (isset($_GET['status']) && $_GET['status'] == 'Tersedia') ? 'selected' : ''; ?>
>
Tersedia
</option>


<option
value="Terjual"
<?= (isset($_GET['status']) && $_GET['status'] == 'Terjual') ? 'selected' : ''; ?>
>
Terjual
</option>

</select>



<!-- URUTAN -->

<select name="urutan" class="form-select form-select-sm">

<option value="">
Upload
</option>


<option
value="terbaru"
<?= (isset($_GET['urutan']) && $_GET['urutan'] == 'terbaru') ? 'selected' : ''; ?>
>
Terbaru
</option>


<option
value="terlama"
<?= (isset($_GET['urutan']) && $_GET['urutan'] == 'terlama') ? 'selected' : ''; ?>
>
Terlama
</option>

</select>



<button type="submit" class="btn btn-primary btn-sm">

Filter

</button>



<a href="index.php" class="btn btn-secondary btn-sm">

Reset

</a>


</form>

</div>



<!-- ========================================
     DATA HEWAN
======================================== -->

<div class="card-body">


<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">


<thead>

<tr>

<th>No</th>

<th>Foto</th>

<th>Nama Hewan</th>

<th>Jenis</th>

<th>Berat</th>

<th>Harga</th>

<th>Peternak</th>

<th>Status</th>

<th>Aksi</th>

</tr>

</thead>


<tbody>


<?php

$no = $offset + 1;


if(mysqli_num_rows($query) > 0){

    while($data = mysqli_fetch_assoc($query)){

?>


<tr>


<!-- NO -->

<td>

<?= $no++; ?>

</td>



<!-- FOTO -->

<td width="90">

<?php

if(!empty($data['foto'])){

?>

<img

src="../../images/hewan/<?= htmlspecialchars($data['foto']); ?>"

width="70"

height="70"

style="object-fit:cover;border-radius:8px;"

>

<?php

}else{

echo "-";

}

?>

</td>



<!-- NAMA -->

<td>

<?= htmlspecialchars($data['nama_hewan']); ?>

</td>



<!-- JENIS -->

<td>

<?= htmlspecialchars($data['jenis']); ?>

</td>



<!-- BERAT -->

<td>

<?= htmlspecialchars($data['berat']); ?> Kg

</td>



<!-- HARGA -->

<td>

Rp <?= number_format($data['harga'],0,',','.'); ?>

</td>



<!-- PETERNAK -->

<td>

<?= htmlspecialchars($data['peternak']); ?>

</td>



<!-- STATUS -->

<td>

<?php

if(isset($data['status'])){

    if($data['status'] == "Terjual"){

        echo '<span class="badge bg-danger">Terjual</span>';

    }else{

        echo '<span class="badge bg-success">Tersedia</span>';

    }

}else{

    echo '<span class="badge bg-primary">Aktif</span>';

}

?>

</td>



<!-- AKSI -->

<td>

<a

href="detail.php?id=<?= $data['id']; ?>"

class="btn btn-info btn-sm"

>

Detail

</a>



<?php

if($data['status'] == "Terjual"){

?>

<a

href="arsip.php?id=<?= $data['id']; ?>"

class="btn btn-warning btn-sm"

onclick="return confirm('Arsipkan data hewan terjual ini? Data tidak akan dihapus.')"

>

<i class="bi bi-archive"></i>

Arsipkan

</a>

<?php } ?>

</td>


</tr>


<?php

    }

}else{

?>


<tr>

<td colspan="9" class="text-center py-4">

Data hewan tidak ditemukan.

</td>

</tr>


<?php

}

?>


</tbody>

</table>

</div>



<!-- ========================================
     PAGINATION
======================================== -->

<?php

if($total_page > 1){

?>


<nav aria-label="Pagination">

<ul class="pagination justify-content-center mt-4">


<!-- SEBELUMNYA -->

<li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">


<a

class="page-link"

href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"

>

&laquo; Sebelumnya

</a>


</li>



<!-- NOMOR HALAMAN -->

<?php

for($i = 1; $i <= $total_page; $i++){

?>


<li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">


<a

class="page-link"

href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>"

>

<?= $i; ?>

</a>


</li>


<?php

}

?>


<!-- SELANJUTNYA -->

<li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">


<a

class="page-link"

href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"

>

Selanjutnya &raquo;

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