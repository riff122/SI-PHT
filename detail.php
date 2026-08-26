<?php

include 'config/koneksi.php';

$id = $_GET['id'];

$stmt = mysqli_prepare($conn,"
SELECT
hewan.*,
users.id AS user_id,
users.nama,
users.wa,
users.email,
users.alamat,
users.desa,
users.foto AS foto_user
FROM hewan
JOIN users ON hewan.user_id = users.id
WHERE hewan.id = ?
");

mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if(!$data){
    echo "<script>
    alert('Hewan tidak ditemukan');
    window.location='home.php';
    </script>";
    exit;
}

$fotos = mysqli_query($conn,"
SELECT foto
FROM hewan_foto
WHERE hewan_id='$id'
ORDER BY RAND()
");

$listFoto=[];

while($row=mysqli_fetch_assoc($fotos)){

    if(!empty($row['foto'])){

        $listFoto[]=$row['foto'];

    }

}


// foto utama detail random
if(count($listFoto)>0){

    $fotoRandom=$listFoto[array_rand($listFoto)];

}else{

    $fotoRandom="default.jpg";

}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">

    <title>
        <?php echo $data['nama_hewan']; ?>
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="style.css">

</head>
<body>

<div class="container py-5">

    <a href="home.php #marketplace"
       class="btn btn-outline-success mb-4">

        ← Kembali Marketplace

    </a>

    <div class="card detail-card shadow">

        <div class="row g-0">

            <!-- FOTO -->
            <div class="col-lg-6">

                <img id="fotoUtama"
src="<?= !empty($fotoRandom) ? 'images/hewan/'.$fotoRandom : 'images/default.jpg'; ?>"
class="detail-image">

                <div class="d-flex flex-wrap gap-2 mt-3">

<?php foreach($listFoto as $foto){ ?>

<?php if(file_exists("images/hewan/".$foto)){ ?>

<img
src="images/hewan/<?php echo $foto; ?>"
width="90"
height="90"
style="
object-fit:cover;
border-radius:10px;
cursor:pointer;
"
onclick="document.getElementById('fotoUtama').src=this.src;"
>

<?php } ?>

<?php } ?>

</div>

            </div>

            <!-- DETAIL -->
            <div class="col-lg-6">

                <div class="p-4 p-lg-5">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span class="badge 
                        <?php echo $data['status'] == 'Tersedia' ? 'bg-success' : 'bg-danger'; ?>
                        badge-status">

                        <?php echo $data['status']; ?>

                    </span>

                    </div>

                        <span class="text-muted">
                        📍 <?= !empty($data['alamat']) ? $data['alamat'] : 'Lokasi belum diisi'; ?>
                        </span>

                        <!-- MAPS -->
<?php if(!empty($data['latitude']) && !empty($data['longitude'])){ ?>

<div class="mb-4">

    <h6 class="fw-bold">
        🗺️ Lokasi Peternak
    </h6>

    <iframe
        width="100%"
        height="250"
        style="border:0; border-radius:15px;"
        loading="lazy"
        allowfullscreen
        src="https://www.google.com/maps?q=<?= $data['latitude']; ?>,<?= $data['longitude']; ?>&output=embed">
    </iframe>


    <a href="https://www.google.com/maps?q=<?= $data['latitude']; ?>,<?= $data['longitude']; ?>"
    target="_blank"
    class="btn btn-outline-success btn-sm mt-3">

        <i class="bi bi-geo-alt"></i>
        Buka Google Maps

    </a>

</div>

<?php } ?>

                    <h1 class="fw-bold mb-3">

                        <?php echo $data['nama_hewan']; ?>

                    </h1>

                    <div class="price mb-4">

                        Rp <?php echo number_format($data['harga']); ?>

                    </div>

                    <!-- INFO -->
                    <div class="row mb-4">

                        <div class="col-6 mb-3">

                            <div class="border rounded-4 p-3 bg-light">

                                <small class="text-muted">
                                    Jenis
                                </small>

                                <h5 class="fw-bold">
                                    <?php echo $data['jenis']; ?>
                                </h5>

                            </div>

                        </div>

                        <div class="col-6 mb-3">

                            <div class="border rounded-4 p-3 bg-light">

                                <small class="text-muted">
                                    Berat
                                </small>

                               <h5 class="fw-bold">
    <?php 
        echo rtrim(
            rtrim(
                number_format($data['berat'], 2, ',', '.'),
                '0'
            ),
            ','
        );
        echo ' ' . $data['satuan_berat'];
    ?>
</h5>

                            </div>

                        </div>

                        <div class="col-6">

                            <div class="border rounded-4 p-3 bg-light">

                                <small class="text-muted">
                                    Umur
                                </small>

                                <h5 class="fw-bold">
                                    <?php echo $data['umur']; ?>
                                </h5>

                            </div>

                        </div>

                       <div class="col-6">

    <div class="border rounded-4 p-3 bg-light">

        <small class="text-muted">
            Peternak
        </small>

        <h5 class="fw-bold mb-1">
            <?= $data['nama']; ?>
        </h5>

        <a href="dashboard/lihat_profil.php?id=<?= $data['user_id']; ?>&balik=<?= $data['id']; ?>"
           class="btn btn-outline-success btn-sm mt-2">

            👤 Lihat Profil

        </a>

    </div>

</div>

                    <!-- DESKRIPSI -->
                    <div class="mb-4">

                        <h4 class="fw-bold mb-3">
                            Deskripsi
                        </h4>

                        <p class="text-muted">

                            <?php echo nl2br($data['deskripsi']); ?>

                        </p>

                    </div>

                    <!-- BUTTON -->
                    <a href="https://wa.me/62<?php echo ltrim($data['wa'], '0'); ?>?text=Saya tertarik dengan <?php echo $data['nama_hewan']; ?>"
                       target="_blank"
                       class="btn btn-success btn-lg w-100 py-3">

                        <i class="bi bi-whatsapp"></i>

                        Chat Peternak

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>