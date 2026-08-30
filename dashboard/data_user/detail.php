<?php

session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../home.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>
        alert('Data user tidak ditemukan.');
        window.location='index.php';
    </script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - <?= htmlspecialchars($data['nama']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="dashboard-admin">

<div class="container mt-5" style="max-width: 600px;">

    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Detail Akun Pengguna</h4>
            <a href="index.php" class="btn btn-outline-light btn-sm">← Kembali</a>
        </div>

        <div class="card-body p-4">
            <div class="text-center mb-4">
                <?php
                $fotoUser = (!empty($data['foto']) && file_exists('../../images/profil/' . $data['foto']))
                    ? '../../images/profil/' . htmlspecialchars($data['foto'])
                    : '../../images/profil/default.png';
?>
                <img src="<?= $fotoUser; ?>"
                     class="rounded-circle shadow"
                     width="120"
                     height="120"
                     style="object-fit: cover;"
                     alt="Foto Profil">
            </div>

            <table class="table table-bordered">
                <tr>
                    <th width="140">Nama</th>
                    <td><?= htmlspecialchars($data['nama']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($data['email']); ?></td>
                </tr>
                <tr>
                    <th>WhatsApp</th>
                    <td><?= htmlspecialchars($data['wa']); ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?= htmlspecialchars($data['alamat'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>
                        <?php if ($data['role'] === 'admin'): ?>
                            <span class="badge bg-danger">Administrator</span>
                        <?php else: ?>
                            <span class="badge bg-success">Peternak</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <?php if ($data['status'] === "aktif"): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Terakhir Login</th>
                    <td><?= htmlspecialchars($data['last_login'] ?? 'Belum pernah login'); ?></td>
                </tr>
            </table>

            <div class="mt-4 text-end">
                <a href="index.php" class="btn btn-secondary">
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

</div>

</body>
</html>