<?php

session_start();

include '../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
}

$user_id = $_SESSION['user']['id'];

$query = mysqli_query($conn,
"SELECT * FROM hewan
WHERE user_id='$user_id'
ORDER BY id DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuntungan Ternak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">

    <div>
        <h3 class="mb-0 fw-bold">
            Laporan Keuntungan
        </h3>
        <small class="text-muted">Rekap laba/rugi ternak Anda</small>
    </div>

    <div class="d-flex gap-2">

        <a href="user.php" class="btn btn-outline-secondary">
            ← Dashboard
        </a>

        <a href="hewan/index.php" class="btn btn-outline-primary">
            Hewan
        </a>

        <a href="pengeluaran/index.php" class="btn btn-outline-warning">
            Pengeluaran
        </a>

    </div>

</div>

    <table class="table table-bordered">

        <tr class="table-success">

            <th>No</th>
            <th>Nama Hewan</th>
            <th>Harga Jual</th>
            <th>Total Pengeluaran</th>
            <th>Keuntungan</th>
            <th>Status</th>

        </tr>

        <?php

        $no = 1;

        while($data = mysqli_fetch_assoc($query)){

            $id_hewan = $data['id'];

            $pengeluaran = mysqli_query($conn,
            "SELECT SUM(nominal) as total
            FROM pengeluaran
            WHERE hewan_id='$id_hewan'");

            $total_pengeluaran = mysqli_fetch_assoc($pengeluaran);

            $total = $total_pengeluaran['total'];

            if(!$total){
                $total = 0;
            }

            $keuntungan = $data['harga'] - $total;

        ?>

        <tr>

            <td><?php echo $no++; ?></td>

            <td>
                <?php echo $data['nama_hewan']; ?>
            </td>

            <td>
                Rp <?php echo number_format($data['harga']); ?>
            </td>

            <td>
                Rp <?php echo number_format($total); ?>
            </td>

            <td>

                <?php

                if($keuntungan > 0){

                    echo "
                    <span class='text-success fw-bold'>
                        Rp ".number_format($keuntungan)."
                    </span>
                    ";

                }else{

                    echo "
                    <span class='text-danger fw-bold'>
                        Rp ".number_format($keuntungan)."
                    </span>
                    ";

                }

                ?>

            </td>

            <td>
    <?php if(strtolower(trim($data['status'])) == 'tersedia'){ ?>

        <span class="badge bg-success">
            Tersedia
        </span>

    <?php }else{ ?>

        <span class="badge bg-danger">
            Terjual
        </span>

    <?php } ?>
</td>

        </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>