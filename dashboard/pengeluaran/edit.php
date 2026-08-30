<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cek kepemilikan data pengeluaran (Cegah IDOR)
$stmt = mysqli_prepare($conn, "
    SELECT p.*, h.nama_hewan 
    FROM pengeluaran p
    JOIN hewan h ON p.hewan_id = h.id
    WHERE p.id = ? AND p.user_id = ?
");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>
        alert('Data pengeluaran tidak ditemukan atau Anda tidak memiliki akses.');
        window.location='index.php';
    </script>";
    exit;
}

// Proses pembaruan data pengeluaran
if (isset($_POST['update'])) {

    $nama = trim($_POST['nama_pengeluaran'] ?? '');
    $nominalRaw = str_replace(',', '', str_replace('.', '', $_POST['nominal'] ?? '0'));
    $nominal = (float)$nominalRaw;
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');

    if (empty($nama) || $nominal <= 0 || empty($tanggal)) {
        echo "<script>
            alert('Semua kolom wajib diisi dengan benar!');
            window.history.back();
        </script>";
        exit;
    }

    $stmtUpdate = mysqli_prepare($conn, "
        UPDATE pengeluaran 
        SET nama_pengeluaran = ?, nominal = ?, tanggal = ?, keterangan = ?
        WHERE id = ? AND user_id = ?
    ");
    mysqli_stmt_bind_param($stmtUpdate, "sdssii", $nama, $nominal, $tanggal, $keterangan, $id, $user_id);

    if (mysqli_stmt_execute($stmtUpdate)) {
        echo "<script>
            alert('Pengeluaran berhasil diupdate');
            window.location='index.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Gagal mengupdate data: " . addslashes(mysqli_error($conn)) . "');
            history.back();
        </script>";
        exit;
    }

}

?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengeluaran - <?= htmlspecialchars($data['nama_hewan']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">

</head>
<body>

<div class="container py-5" style="max-width: 700px;">

    <div class="card border-0 shadow-sm p-4">

        <h2 class="mb-4 fw-bold">
            Edit Pengeluaran
        </h2>

        <p class="text-muted mb-4">
            Hewan Terkait: <strong class="text-success"><?= htmlspecialchars($data['nama_hewan']); ?></strong>
        </p>

        <form method="POST">

            <!-- Nama Pengeluaran -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Pengeluaran</label>
                <input type="text"
                       name="nama_pengeluaran"
                       class="form-control"
                       value="<?= htmlspecialchars($data['nama_pengeluaran']); ?>"
                       required>
            </div>

            <!-- Nominal -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Nominal (Rp)</label>
                <input type="text"
                       name="nominal"
                       id="nominal"
                       class="form-control"
                       value="<?= number_format($data['nominal'], 0, ',', '.'); ?>"
                       inputmode="numeric"
                       required>
            </div>

            <!-- Tanggal -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal</label>
                <input type="date"
                       name="tanggal"
                       class="form-control"
                       value="<?= htmlspecialchars($data['tanggal']); ?>"
                       required>
            </div>

            <!-- Keterangan -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Keterangan</label>
                <textarea name="keterangan"
                          class="form-control"
                          rows="4"
                          required><?= htmlspecialchars($data['keterangan']); ?></textarea>
            </div>

            <!-- Tombol -->
            <div class="d-flex gap-2">
                <button type="submit"
                        name="update"
                        class="btn btn-success px-4">
                    Update Pengeluaran
                </button>

                <a href="index.php"
                   class="btn btn-secondary px-4">
                    Kembali
                </a>
            </div>

        </form>

    </div>

</div>

<script>
const nominalInput = document.getElementById('nominal');

nominalInput.addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if(value){
        value = Number(value).toLocaleString('id-ID');
    }
    this.value = value;
});

document.querySelector('form').addEventListener('submit', function() {
    nominalInput.value = nominalInput.value.replace(/\./g, '').replace(/,/g, '');
});
</script>

</body>
</html>