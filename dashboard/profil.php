<?php

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

// Ambil data user saat ini
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: ../home.php");
    exit;
}

// Proses Simpan Profil
if (isset($_POST['simpan'])) {

    $nama   = trim($_POST['nama'] ?? '');
    $wa     = trim($_POST['wa'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    $fotoBaru = $user['foto'];

    // Proses upload foto profil jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {

        $ekstensiOk = ['jpg', 'jpeg', 'png', 'webp'];
        $namaAsli   = $_FILES['foto']['name'];
        $ekstensi   = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ekstensiOk)) {

            $namaFile = 'user_' . $user_id . '_' . time() . '.' . $ekstensi;
            $folderTujuan = '../images/profil/';
            $tujuanFull   = $folderTujuan . $namaFile;

            if (!is_dir($folderTujuan)) {
                mkdir($folderTujuan, 0755, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuanFull)) {
                // Hapus foto lama jika ada
                if (!empty($user['foto']) && file_exists($folderTujuan . $user['foto'])) {
                    unlink($folderTujuan . $user['foto']);
                }
                $fotoBaru = $namaFile;
            }
        }
    }

    $stmtUpdate = mysqli_prepare($conn, "
        UPDATE users
        SET nama = ?, wa = ?, alamat = ?, foto = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmtUpdate, "ssssi", $nama, $wa, $alamat, $fotoBaru, $user_id);

    if (mysqli_stmt_execute($stmtUpdate)) {
        $_SESSION['user']['nama'] = $nama;
        echo "<script>
            alert('Profil berhasil diperbarui');
            window.location='profil.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Gagal memperbarui profil: " . addslashes(mysqli_error($conn)) . "');
            history.back();
        </script>";
        exit;
    }
}

// Foto profil untuk ditampilkan
$folderFoto = '../images/profil/';
if (!empty($user['foto']) && file_exists($folderFoto . $user['foto'])) {
    $tampilFoto = $folderFoto . htmlspecialchars($user['foto']);
} else {
    $tampilFoto = '../images/profil/default.png';
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?= htmlspecialchars($user['nama']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>

<body class="bg-light">

    <div class="container py-5" style="max-width: 650px;">

        <!-- Tombol Kembali -->
        <div class="mb-4">
            <a href="user.php" class="btn btn-outline-secondary btn-sm">
                ← Kembali ke Dashboard
            </a>
        </div>

        <div class="card border-0 shadow-sm p-4">

            <!-- Avatar & Modal Preview -->
            <div class="text-center mb-3">
                <img src="<?= $tampilFoto; ?>"
                    class="rounded-circle shadow-sm"
                    width="140"
                    height="140"
                    style="object-fit: cover; cursor: pointer;"
                    data-bs-toggle="modal"
                    data-bs-target="#modalFoto"
                    alt="Foto Profil">
                <div class="small text-muted mt-2">Klik foto untuk memperbesar</div>
            </div>

            <h3 class="fw-bold text-center mb-4">Profil Saya</h3>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text"
                        name="nama"
                        class="form-control"
                        value="<?= htmlspecialchars($user['nama']); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email"
                        class="form-control bg-light"
                        value="<?= htmlspecialchars($user['email']); ?>"
                        readonly>
                    <small class="text-muted">Email tidak dapat diubah.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor WhatsApp</label>
                    <input type="text"
                        name="wa"
                        class="form-control"
                        value="<?= htmlspecialchars($user['wa']); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap</label>
                    <textarea name="alamat"
                        class="form-control"
                        rows="3"
                        required><?= htmlspecialchars($user['alamat']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Ganti Foto Profil</label>
                    <input type="file"
                        name="foto"
                        class="form-control"
                        accept="image/*">
                    <small class="text-muted">Format: JPG, JPEG, PNG, WEBP.</small>
                </div>

                <button type="submit"
                    name="simpan"
                    class="btn btn-success w-100 py-2 fw-semibold">
                    Simpan Perubahan
                </button>

            </form>

        </div>

    </div>

    <!-- Modal Zoom Foto Profil -->
    <div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    <img src="<?= $tampilFoto; ?>"
                        class="img-fluid rounded shadow-lg"
                        style="max-height: 80vh; object-fit: contain;"
                        alt="Zoom Foto">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>