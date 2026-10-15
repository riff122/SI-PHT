<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$query = mysqli_query($conn,
"SELECT pengeluaran.*, hewan.nama_hewan
FROM pengeluaran
JOIN hewan ON pengeluaran.hewan_id = hewan.id
WHERE hewan.user_id='$user_id'
ORDER BY pengeluaran.id DESC");

?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

</head>
<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">

    <h3 class="mb-0 fw-bold text-dark">
        Data Pengeluaran
    </h3>

    <div class="d-flex gap-2">
        <a href="../hewan/index.php" class="btn btn-outline-secondary">
            ← Kembali
        </a>

        <a href="tambah.php" class="btn btn-success">
            + Tambah Pengeluaran
        </a>
    </div>

</div>

    <table class="table table-bordered bg-white">

        <thead class="table-success">

            <tr>
                <th>No</th>
                <th>Nama Hewan</th>
                <th>Nama Pengeluaran</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>

        </thead>

        <tbody>

        <?php
        $no = 1;

        while($data = mysqli_fetch_assoc($query)){
        ?>

            <tr>

                <td><?php echo $no++; ?></td>

                <td>
                <?php echo $data['nama_hewan']; ?>
                </td>

                <td>
                <?php echo $data['nama_pengeluaran']; ?>
                </td>

                <td>
                <?php echo $data['keterangan']; ?>
                </td>

                <td>
                Rp <?php echo number_format($data['nominal']); ?>
                </td>

                <td>
                    <?php echo $data['tanggal']; ?>
                </td>

                <td>
                   <a href="edit.php?id=<?php echo $data['id']; ?>"
                    class="btn btn-warning btn-sm">

                    Edit

                    </a> 


                    <a href="hapus.php?id=<?php echo $data['id']; ?>"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Yakin ingin hapus?')">

                        Hapus

                    </a>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

</body>
</html>