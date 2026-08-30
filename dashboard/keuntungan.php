<?php

session_start();

include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

// Ambil data hewan beserta total pengeluaran menggunakan LEFT JOIN (Efisien tanpa N+1 query)
$stmt = mysqli_prepare($conn, "
    SELECT 
        h.id,
        h.nama_hewan,
        h.harga,
        h.status,
        COALESCE(SUM(p.nominal), 0) AS total_pengeluaran
    FROM hewan h
    LEFT JOIN pengeluaran p ON h.id = p.hewan_id
    WHERE h.user_id = ?
    GROUP BY h.id
    ORDER BY h.id DESC
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
    <title>Laporan Keuntungan Ternak - Dashboard Peternak</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 p-4 bg-white rounded shadow-sm gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-dark">
                    Laporan Keuntungan Ternak
                </h3>
                <small class="text-muted">Rekapitulasi laba dan rugi dari seluruh hewan ternak Anda</small>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="user.php" class="btn btn-outline-secondary btn-sm">
                    ← Dashboard
                </a>
                <a href="hewan/index.php" class="btn btn-outline-primary btn-sm">
                    Data Hewan
                </a>
                <a href="pengeluaran/index.php" class="btn btn-outline-warning btn-sm">
                    Data Pengeluaran
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th width="60" class="text-center">No</th>
                                <th>Nama Hewan</th>
                                <th>Harga Jual (Target / Riil)</th>
                                <th>Total Pengeluaran</th>
                                <th>Keuntungan / Kerugian</th>
                                <th width="120">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;
if (mysqli_num_rows($query) === 0) {
    ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada data hewan ternak.
                                    </td>
                                </tr>
                            <?php
}
while ($data = mysqli_fetch_assoc($query)) {
    $total = (float)$data['total_pengeluaran'];
    $keuntungan = (float)$data['harga'] - $total;
    ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($data['nama_hewan']); ?>
                                    </td>
                                    <td>
                                        Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-danger">
                                        Rp <?= number_format($total, 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php if ($keuntungan >= 0) { ?>
                                            <span class="text-success fw-bold">
                                                + Rp <?= number_format($keuntungan, 0, ',', '.'); ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="text-danger fw-bold">
                                                - Rp <?= number_format(abs($keuntungan), 0, ',', '.'); ?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (strtolower(trim($data['status'])) === 'tersedia') { ?>
                                            <span class="badge bg-success">Tersedia</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Terjual</span>
                                        <?php } ?>
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