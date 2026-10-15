<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$id = (int)$_GET['id'];

$query = mysqli_query($conn,
"SELECT * FROM hewan
WHERE id='$id'
AND user_id='$user_id'");

$data = mysqli_fetch_assoc($query);

if(!$data){

    echo "
    <script>
        alert('Data tidak ditemukan');
        window.location='index.php';
    </script>
    ";

    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hewan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container py-5">

    <div class="card border-0 shadow-sm">

        <div class="card-body p-5">

            <h2 class="mb-4">
                Edit Hewan
            </h2>

            <form action="update.php?id=<?php echo $id; ?>" method="POST">

                <div class="mb-3">
                    <label>Nama Hewan</label>
                    <input type="text"
                           name="nama_hewan"
                           class="form-control"
                           required
                           value="<?php echo $data['nama_hewan']; ?>">
                </div>

                <div class="mb-3">

                    <label>Jenis</label>

                    <select name="jenis" class="form-select">

                        <option value="Jantan"
                        <?php if($data['jenis']=='Jantan') echo 'selected'; ?>>
                        Jantan
                        </option>

                        <option value="Betina"
                        <?php if($data['jenis']=='Betina') echo 'selected'; ?>>
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
               value="<?php echo $data['berat']; ?>">

        <select name="satuan_berat"
                class="form-select"
                style="max-width: 120px;">

            <option value="kg"
                <?php if(isset($data['satuan_berat']) && $data['satuan_berat']=='kg') echo 'selected'; ?>>
                Kg
            </option>

            <option value="gram"
                <?php if(isset($data['satuan_berat']) && $data['satuan_berat']=='gram') echo 'selected'; ?>>
                Gram
            </option>

        </select>
    </div>

    <small class="text-muted">
        Contoh: 0,8 Kg atau 750 Gram
    </small>
</div>
                <div class="mb-3">
                    <label>Umur</label>
                    <input type="text"
                           name="umur"
                           class="form-control"
                           required
                           value="<?php echo $data['umur']; ?>">
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
               value="<?php echo number_format($data['harga'], 0, ',', '.'); ?>">
    </div>

    <small class="text-muted">
        Harga akan otomatis menggunakan format titik.
    </small>
</div>


                <div class="mb-3">
                    <label>Status</label>

                    <select name="status" class="form-select">

                        <option value="Tersedia"
                        <?php if($data['status']=='Tersedia') echo 'selected'; ?>>
                        Tersedia
                        </option>

                        <option value="Terjual"
                        <?php if($data['status']=='Terjual') echo 'selected'; ?>>
                        Terjual
                        </option>

                    </select>

                </div>

                <div class="mb-3">
                    <label>Deskripsi</label>

                    <textarea name="deskripsi"
                              class="form-control"
                              rows="5"><?php echo $data['deskripsi']; ?></textarea>
                </div>

                <div class="mb-3">

                    <label>Foto Saat Ini</label>

                    <br>

                    <img src="../../assets/images/<?php echo $data['foto']; ?>"
                         class="preview mb-3">

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

inputHarga.addEventListener('input', function () {

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