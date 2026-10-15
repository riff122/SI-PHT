<?php

session_start();

include __DIR__ . '/../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'terbaru';

$whereStatus = "";

if($status != ""){
    $whereStatus = "AND hewan.status='$status'";
}

$sort = $_GET['sort'] ?? 'terbaru';

switch($sort){

    case 'terlama':
        $order = "hewan.created_at ASC";
        break;

    case 'harga_tertinggi':
        $order = "hewan.harga DESC";
        break;

    case 'harga_terendah':
        $order = "hewan.harga ASC";
        break;

    default:
        $order = "hewan.updated_at DESC";
        break;
}


$query = mysqli_query($conn,
"SELECT 
    hewan.*,

    COALESCE(SUM(pengeluaran.nominal),0) 
    AS total_pengeluaran

FROM hewan

LEFT JOIN pengeluaran
ON hewan.id = pengeluaran.hewan_id

WHERE hewan.user_id='$user_id'
$whereStatus

GROUP BY hewan.id

ORDER BY $order

    ");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Hewan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="page-title">
            Data Hewan
            </h2>

            <p class="page-subtitle">
            Kelola hewan Ternak Anda
            </p>

             <a href="../user.php" class="btn btn-outline-secondary">
            ← Dashboard
            </a>
        </div>

        <a href="tambah.php" class="btn btn-success">
            + Tambah Hewan
        </a>

    </div>

    <div class="card border-0 shadow-sm">

    <div class="card-body">

        <!-- Dropdown Urutkan -->
<form method="GET" class="row mb-3">

    <div class="col-md-3">
        <select name="status" class="form-select" onchange="this.form.submit()">

            <option value="">Semua Status</option>

            <option value="Tersedia"
                <?= ($status=='Tersedia') ? 'selected' : ''; ?>>
                Tersedia
            </option>

            <option value="Terjual"
                <?= ($status=='Terjual') ? 'selected' : ''; ?>>
                Terjual
            </option>

        </select>
    </div>

    <div class="col-md-3">
        <select name="sort" class="form-select" onchange="this.form.submit()">

            <option value="terbaru"
                <?= ($sort=='terbaru') ? 'selected' : ''; ?>>
                Terbaru
            </option>

            <option value="terlama"
                <?= ($sort=='terlama') ? 'selected' : ''; ?>>
                Terlama
            </option>

            <option value="harga_tertinggi"
                <?= ($sort=='harga_tertinggi') ? 'selected' : ''; ?>>
                Harga Tertinggi
            </option>

            <option value="harga_terendah"
                <?= ($sort=='harga_terendah') ? 'selected' : ''; ?>>
                Harga Terendah
            </option>

        </select>
    </div>

</form>

        <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Hewan</th>
                            <th>Jenis Kelamin</th>
                            <th>Berat</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tanggal Terjual</th>
                            <th>Aksi</th>
                            
                        </tr>
                    </thead>

                    <tbody>

                    <?php while($data = mysqli_fetch_assoc($query)) : ?>
                    
                        
<?php
$total_pengeluaran = $data['total_pengeluaran'] ?? 0;

$hasil = $data['harga'] - $total_pengeluaran;
?>   
                        <tr>
                            <td>
                                <img src="../../images/hewan/<?php echo $data['foto']; ?>"
                                style="width:90px; height:90px; object-fit:cover; border-radius:8px; cursor:pointer;"
                                onclick="openImage(this.src)">
                            </td>

                            <td>
                                <?php echo $data['nama_hewan']; ?>
                            </td>

                            <td>
                                <?php echo $data['jenis']; ?>
                            </td>

                            <td>
    <?php echo $data['berat']; ?>
    <?php echo $data['satuan_berat']; ?>
</td>

                            <td>

    <div>
        Harga Jual :
        Rp <?php echo number_format($data['harga']); ?>
    </div>


    <div class="text-danger small">
        Pengeluaran :
        Rp <?php echo number_format($data['total_pengeluaran']); ?>
    </div>

    <?php if($hasil >= 0){ ?>

        <div class="text-success fw-bold small">
            Estimasi Keuntungan :
            Rp <?php echo number_format($hasil); ?>
        </div>

    <?php }else{ ?>

        <div class="text-danger fw-bold small">
            Estimasi Kerugian :
            Rp <?php echo number_format(abs($hasil)); ?>
        </div>

    <?php } ?>

    <td>

    <?php if($data['status'] == 'Tersedia'){ ?>

        <span class="badge bg-success">
            Tersedia
        </span>

    <?php }else{ ?>

        <span class="badge bg-danger">
            Terjual
        </span>

    <?php } ?>

</td>

<td>

    <?php

    if($data['tanggal_terjual'] != NULL){

        echo date('d-m-Y H:i', strtotime($data['tanggal_terjual']));

    }else{

        echo "-";

    }

    ?>

</td>

<td>

    <a href="edit.php?id=<?=$data['id'];?>" class="btn btn-warning btn-sm">
        Edit
    </a>

    <a href="../pengeluaran/tambah.php?hewan_id=<?=$data['id'];?>"
       class="btn btn-primary btn-sm">
       + Pengeluaran
    </a>

    <a href="hapus.php?id=<?=$data['id'];?>"
       class="btn btn-danger btn-sm"
       onclick="return confirm('Yakin ingin menghapus?')">
       Hapus
    </a>

</td>

</td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
<div id="imgModal" 
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.8); justify-content:center; align-items:center;
            z-index:9999;">

    <img id="imgPreview" 
         style="max-width:90%; max-height:90%; border-radius:10px;">
</div>

<script>
function openImage(src) {
    document.getElementById('imgModal').style.display = 'flex';
    document.getElementById('imgPreview').src = src;
}

document.getElementById('imgModal').onclick = function () {
    this.style.display = 'none';
};
</script>
</body>
</html>