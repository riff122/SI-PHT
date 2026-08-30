<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

$stmt = mysqli_prepare($conn, "
    SELECT pengeluaran.*, hewan.nama_hewan
    FROM pengeluaran
    JOIN hewan ON pengeluaran.hewan_id = hewan.id
    WHERE hewan.user_id = ?
    ORDER BY pengeluaran.id DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengeluaran - Dashboard Peternak</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">

</head>
<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">

        <div>
            <h3 class="mb-0 fw-bold text-dark">
                Data Pengeluaran
            </h3>
            <small class="text-muted">Catatan seluruh biaya modal dan operasional ternak Anda</small>
        </div>

        <div class="d-flex gap-2">
            <a href="../hewan/index.php" class="btn btn-outline-secondary">
                ← Data Hewan
            </a>

            <a href="tambah.php" class="btn btn-success">
                + Tambah Pengeluaran
            </a>
        </div>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle bg-white mb-0">

                    <thead class="table-success">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Hewan</th>
                            <th>Nama Pengeluaran</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $no = 1;
if (mysqli_num_rows($query) === 0) {
    ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada data pengeluaran yang tercatat.
                            </td>
                        </tr>
                    <?php
}
while ($data = mysqli_fetch_assoc($query)) {
    ?>

                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($data['nama_hewan']); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($data['nama_pengeluaran']); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($data['keterangan']); ?>
                            </td>
                            <td class="fw-bold text-danger">
                                Rp <?= number_format($data['nominal'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($data['tanggal']); ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $data['id']; ?>"
                                   class="btn btn-warning btn-sm">
                                    Edit
                                </a> 

                                <a href="hapus.php?id=<?= $data['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus data pengeluaran ini?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

</body>
</html>