<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM hewan WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "
    <script>
        alert('Data tidak ditemukan atau Anda tidak memiliki akses.');
        window.location='index.php';
    </script>
    ";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hewan - <?= htmlspecialchars($data['nama_hewan']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">

</head>

<body>

    <div class="container py-5">

        <div class="card border-0 shadow-sm">

            <div class="card-body p-5">

                <h2 class="mb-4">
                    Edit Hewan
                </h2>

                <form action="update.php?id=<?= $id; ?>" method="POST">

                    <div class="mb-3">
                        <label>Nama Hewan</label>
                        <input type="text"
                            name="nama_hewan"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($data['nama_hewan']); ?>">
                    </div>

                    <div class="mb-3">
                        <label>Jenis Kelamin</label>
                        <select name="jenis" class="form-select">
                            <option value="Jantan" <?= ($data['jenis'] == 'Jantan') ? 'selected' : ''; ?>>
                                Jantan
                            </option>
                            <option value="Betina" <?= ($data['jenis'] == 'Betina') ? 'selected' : ''; ?>>
                                Betina
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Berat</label>
                        <div class="input-group">
                            <input type="number"
                                name="berat"
                                class="form-control"
                                step="0.01"
                                min="0"
                                required
                                value="<?= htmlspecialchars($data['berat']); ?>">

                            <select name="satuan_berat"
                                class="form-select"
                                style="max-width: 120px;">
                                <option value="kg" <?= (isset($data['satuan_berat']) && $data['satuan_berat'] == 'kg') ? 'selected' : ''; ?>>
                                    Kg
                                </option>
                                <option value="gram" <?= (isset($data['satuan_berat']) && $data['satuan_berat'] == 'gram') ? 'selected' : ''; ?>>
                                    Gram
                                </option>
                            </select>
                        </div>
                        <small class="text-muted">
                            Contoh: 0.8 Kg atau 750 Gram
                        </small>
                    </div>

                    <div class="mb-3">
                        <label>Umur</label>
                        <input type="text"
                            name="umur"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($data['umur']); ?>">
                    </div>

                    <div class="mb-3">
                        <label>Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text"
                                id="harga"
                                name="harga"
                                class="form-control"
                                inputmode="numeric"
                                required
                                value="<?= number_format($data['harga'], 0, ',', '.'); ?>">
                        </div>
                        <small class="text-muted">
                            Harga akan otomatis menggunakan format titik.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="Tersedia" <?= ($data['status'] == 'Tersedia') ? 'selected' : ''; ?>>
                                Tersedia
                            </option>
                            <option value="Terjual" <?= ($data['status'] == 'Terjual') ? 'selected' : ''; ?>>
                                Terjual
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi"
                            class="form-control"
                            rows="5"><?= htmlspecialchars($data['deskripsi']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Foto Saat Ini</label>
                        <br>
                        <?php if (!empty($data['foto']) && file_exists('../../images/hewan/' . $data['foto'])): ?>
                            <img src="../../images/hewan/<?= htmlspecialchars($data['foto']); ?>"
                                class="preview mb-3 rounded"
                                style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <p class="text-muted">Belum ada foto utama.</p>
                        <?php endif; ?>
                    </div>

                    <button type="submit"
                        name="update"
                        class="btn btn-success">
                        Update Data
                    </button>

                    <a href="index.php"
                        class="btn btn-secondary">
                        Kembali
                    </a>

                </form>

            </div>

        </div>

    </div>

    <script>
        const inputHarga = document.getElementById('harga');

        inputHarga.addEventListener('input', function() {

            let angka = this.value.replace(/\D/g, '');

            if (angka === '') {
                this.value = '';
                return;
            }

            this.value = new Intl.NumberFormat('id-ID').format(angka);
        });
    </script>

</body>

</html>