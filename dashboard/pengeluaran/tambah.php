<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$hewan_id = isset($_GET['hewan_id']) ? (int)$_GET['hewan_id'] : 0;

// Ambil daftar semua hewan milik peternak ini untuk dropdown atau validasi
$stmtDaftar = mysqli_prepare($conn, "SELECT id, nama_hewan, jenis FROM hewan WHERE user_id = ? ORDER BY nama_hewan ASC");
mysqli_stmt_bind_param($stmtDaftar, "i", $user_id);
mysqli_stmt_execute($stmtDaftar);
$resDaftar = mysqli_stmt_get_result($stmtDaftar);

$listHewan = [];
while ($h = mysqli_fetch_assoc($resDaftar)) {
    $listHewan[] = $h;
}

if (empty($listHewan)) {
    echo "<script>
        alert('Anda belum memiliki data hewan. Silakan tambahkan hewan terlebih dahulu.');
        window.location='../hewan/tambah.php';
    </script>";
    exit;
}

// Cek apakah hewan_id spesifik valid jika dikirim lewat GET
$hewanTerpilih = null;
if ($hewan_id > 0) {
    foreach ($listHewan as $lh) {
        if ((int)$lh['id'] === $hewan_id) {
            $hewanTerpilih = $lh;
            break;
        }
    }
    if (!$hewanTerpilih) {
        echo "<script>
            alert('Data hewan tidak ditemukan atau Anda tidak memiliki akses.');
            window.location='index.php';
        </script>";
        exit;
    }
}

// Proses Simpan Pengeluaran
if (isset($_POST['submit'])) {

    $targetHewanId = isset($_POST['hewan_id']) ? (int)$_POST['hewan_id'] : $hewan_id;
    $nama = trim($_POST['nama_pengeluaran'] ?? '');
    $nominalRaw = str_replace(',', '', str_replace('.', '', $_POST['nominal'] ?? '0'));
    $nominal = (float)$nominalRaw;
    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($targetHewanId <= 0 || empty($nama) || $nominal <= 0 || empty($tanggal)) {
        echo "<script>
            alert('Semua field wajib diisi dengan benar!');
            window.history.back();
        </script>";
        exit;
    }

    // Validasi kepemilikan hewan target
    $stmtCekMilikh = mysqli_prepare($conn, "SELECT id FROM hewan WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmtCekMilikh, "ii", $targetHewanId, $user_id);
    mysqli_stmt_execute($stmtCekMilikh);
    $resCekMilik = mysqli_stmt_get_result($stmtCekMilikh);

    if (mysqli_num_rows($resCekMilik) === 0) {
        echo "<script>
            alert('Data hewan target tidak valid!');
            window.history.back();
        </script>";
        exit;
    }

    // Insert pengeluaran dengan Prepared Statement
    $stmtInsert = mysqli_prepare($conn, "
        INSERT INTO pengeluaran (hewan_id, user_id, nama_pengeluaran, nominal, tanggal, keterangan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmtInsert, "iisdss", $targetHewanId, $user_id, $nama, $nominal, $tanggal, $keterangan);

    if (mysqli_stmt_execute($stmtInsert)) {
        echo "<script>
            alert('Pengeluaran berhasil ditambahkan!');
            window.location='index.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Gagal menyimpan pengeluaran: " . addslashes(mysqli_error($conn)) . "');
            window.history.back();
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
    <title>Tambah Pengeluaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">

    <style>
    body {
        background: #0f172a;
        color: #f8fafc;
    }
    .form-wrapper {
        width: 100%;
        max-width: 750px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    .form-box {
        background: rgba(30, 41, 59, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 35px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    }
    .form-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #ffffff;
        text-align: center;
    }
    .form-subtitle {
        color: #94a3b8;
        margin-bottom: 30px;
        text-align: center;
    }
    label {
        margin-bottom: 8px;
        font-weight: 500;
        color: #e2e8f0;
    }
    .form-control, .form-select {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #f8fafc !important;
        padding: 10px 14px;
        border-radius: 8px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #22c55e !important;
        box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, .25) !important;
    }
    </style>

</head>

<body>
    
<div class="form-wrapper">

    <div class="form-box">

        <h1 class="form-title">
            Tambah Pengeluaran
        </h1>

        <p class="form-subtitle">
            Catat seluruh biaya kebutuhan ternak Anda dengan rapi
        </p>

        <form method="POST">

            <!-- Pilihan Hewan -->
            <div class="mb-4">
                <label>Pilih Hewan</label>
                <select name="hewan_id" class="form-select" required>
                    <?php if (!$hewanTerpilih): ?>
                        <option value="">-- Pilih Hewan Terkait --</option>
                    <?php endif; ?>
                    <?php foreach ($listHewan as $h): ?>
                        <option value="<?= $h['id']; ?>" <?= ($hewanTerpilih && $hewanTerpilih['id'] == $h['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($h['nama_hewan']); ?> (<?= htmlspecialchars($h['jenis']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label>Nama Pengeluaran</label>
                <input type="text"
                       name="nama_pengeluaran"
                       class="form-control"
                       placeholder="Contoh: Pakan Konsentrat, Vitamin, Perawatan Kandang"
                       required>
            </div>

            <div class="mb-4">
                <label>Nominal (Rp)</label>
                <input type="text"
                       name="nominal"
                       id="nominal"
                       class="form-control"
                       placeholder="Contoh: 150.000"
                       inputmode="numeric"
                       required>
            </div>

            <div class="mb-4">
                <label>Tanggal</label>
                <input type="date"
                       name="tanggal"
                       class="form-control"
                       value="<?= date('Y-m-d'); ?>"
                       required>
            </div>

            <div class="mb-4">
                <label>Keterangan</label>
                <textarea class="form-control"
                          rows="4"
                          name="keterangan"
                          required
                          placeholder="Tambahkan catatan detail kebutuhan"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit"
                        name="submit"
                        class="btn btn-success px-4 py-2">
                    Simpan Pengeluaran
                </button>

                <a href="index.php"
                   class="btn btn-secondary px-4 py-2">
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