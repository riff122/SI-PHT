<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../../home.php");
    exit;
}

// ========================================
// FILTER & QUERY
// ========================================
$where = [];

// Filter nama peternak
if (isset($_GET['peternak']) && $_GET['peternak'] != "") {
    $peternak = mysqli_real_escape_string($conn, $_GET['peternak']);
    $where[] = "users.nama = '$peternak'";
}

// Filter status
if (isset($_GET['status']) && $_GET['status'] != "") {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where[] = "hewan.status = '$status'";
}

$whereClause = "";
if (count($where) > 0) {
    $whereClause = " AND " . implode(" AND ", $where);
}

// Urutan data
$orderClause = " ORDER BY hewan.id DESC";
if (isset($_GET['urutan'])) {
    if ($_GET['urutan'] == "terlama") {
        $orderClause = " ORDER BY hewan.id ASC";
    }
}

// ========================================
// PAGINATION
// ========================================
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Hitung total data
$count_sql = "
    SELECT COUNT(*) AS total
    FROM hewan
    JOIN users ON hewan.user_id = users.id
    WHERE hewan.status_arsip = 0 $whereClause
";
$count_query = mysqli_query($conn, $count_sql);
$total_data = mysqli_fetch_assoc($count_query)['total'];
$total_page = ceil($total_data / $limit);

// Query data utama
$sql = "
    SELECT hewan.*, users.nama AS peternak
    FROM hewan
    JOIN users ON hewan.user_id = users.id
    WHERE hewan.status_arsip = 0 $whereClause
    $orderClause
    LIMIT $limit OFFSET $offset
";
$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Hewan - Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../style.css">
</head>

<body class="dashboard-admin">

<div class="container mt-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-1">Data Hewan</h2>
            <p class="text-muted mb-0">Kelola seluruh postingan hewan ternak dari peternak</p>
        </div>

        <div class="d-flex gap-2">
            <a href="arsip/index.php" class="btn btn-outline-warning">
                <i class="bi bi-archive me-1"></i> Arsip Hewan Terjual
            </a>
            <a href="../admin.php" class="btn btn-secondary">
                ← Kembali
            </a>
        </div>
    </div>

    <div class="card shadow border-0">

        <!-- FILTER BAR -->
        <div class="card-header bg-white py-3">
            <form method="GET" class="row g-2 align-items-center">

                <!-- Peternak -->
                <div class="col-md-3">
                    <select name="peternak" class="form-select form-select-sm">
                        <option value="">Semua Peternak</option>
                        <?php
                        $list_peternak = mysqli_query($conn, "
                            SELECT DISTINCT users.nama
                            FROM users
                            JOIN hewan ON users.id = hewan.user_id
                            WHERE users.role='peternak'
                            ORDER BY users.nama ASC
                        ");
while ($p = mysqli_fetch_assoc($list_peternak)) {
    ?>
                            <option value="<?= htmlspecialchars($p['nama']); ?>"
                                <?= (isset($_GET['peternak']) && $_GET['peternak'] == $p['nama']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($p['nama']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" <?= (isset($_GET['status']) && $_GET['status'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="Terjual" <?= (isset($_GET['status']) && $_GET['status'] == 'Terjual') ? 'selected' : ''; ?>>Terjual</option>
                    </select>
                </div>

                <!-- Urutan -->
                <div class="col-md-2">
                    <select name="urutan" class="form-select form-select-sm">
                        <option value="terbaru" <?= (isset($_GET['urutan']) && $_GET['urutan'] == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="terlama" <?= (isset($_GET['urutan']) && $_GET['urutan'] == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        <!-- TABEL DATA -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Foto</th>
                            <th>Nama Hewan</th>
                            <th>Jenis</th>
                            <th>Berat</th>
                            <th>Harga</th>
                            <th>Peternak</th>
                            <th>Status</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    $no = $offset + 1;
if (mysqli_num_rows($query) > 0) {
    while ($data = mysqli_fetch_assoc($query)) {
        $fotoPath = (!empty($data['foto']) && file_exists('../../images/hewan/' . $data['foto']))
            ? '../../images/hewan/' . htmlspecialchars($data['foto'])
            : '../../images/default.jpg';
        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td width="90">
                                <img src="<?= $fotoPath; ?>"
                                     width="70"
                                     height="70"
                                     style="object-fit: cover; border-radius: 8px;"
                                     alt="Foto">
                            </td>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($data['nama_hewan']); ?>
                            </td>
                            <td><?= htmlspecialchars($data['jenis']); ?></td>
                            <td><?= htmlspecialchars($data['berat']); ?> <?= htmlspecialchars($data['satuan_berat'] ?? 'Kg'); ?></td>
                            <td>Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($data['peternak']); ?></td>
                            <td>
                                <?php if ($data['status'] == "Terjual"): ?>
                                    <span class="badge bg-danger">Terjual</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="detail.php?id=<?= $data['id']; ?>" class="btn btn-info btn-sm">
                                    Detail
                                </a>

                                <?php if ($data['status'] == "Terjual"): ?>
                                    <a href="arsip.php?id=<?= $data['id']; ?>"
                                       class="btn btn-warning btn-sm"
                                       onclick="return confirm('Arsipkan data hewan terjual ini?')">
                                        <i class="bi bi-archive"></i> Arsip
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
    }
} else {
    ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Data hewan tidak ditemukan.
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($total_page > 1): ?>
                <div class="p-3 border-top">
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    &laquo; Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_page; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($page >= $total_page) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    Selanjutnya &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>