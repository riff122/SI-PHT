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
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
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
    <title>Data User - Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>

<body class="dashboard-admin">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Data Pengguna (User)</h2>
            <p class="text-muted mb-0">Kelola akun administrator dan peternak terdaftar</p>
        </div>

        <a href="../admin.php" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th width="220">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    $no = $offset + 1;
while ($data = mysqli_fetch_assoc($query)) {
    ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($data['nama']); ?></td>
                            <td><?= htmlspecialchars($data['email']); ?></td>
                            <td><?= htmlspecialchars($data['wa']); ?></td>
                            <td>
                                <?php if ($data['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Peternak</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['status'] === "aktif"): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="detail.php?id=<?= $data['id']; ?>" class="btn btn-info btn-sm text-white">
                                    Detail
                                </a>

                                <?php if ($data['id'] != $_SESSION['user']['id']): ?>
                                    <?php if ($data['status'] === "aktif"): ?>
                                        <a href="status.php?id=<?= $data['id']; ?>&aksi=nonaktif"
                                           class="btn btn-warning btn-sm"
                                           onclick="return confirm('Nonaktifkan akun ini?')">
                                            Nonaktif
                                        </a>
                                    <?php else: ?>
                                        <a href="status.php?id=<?= $data['id']; ?>&aksi=aktif"
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Aktifkan kembali akun ini?')">
                                            Aktifkan
                                        </a>
                                    <?php endif; ?>

                                    <a href="hapus.php?id=<?= $data['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus permanen akun user ini?')">
                                        Hapus
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPage > 1): ?>
                <div class="p-3 border-top">
                    <nav>
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page - 1; ?>">
                                    &laquo; Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPage; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $i; ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($page >= $totalPage) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page + 1; ?>">
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