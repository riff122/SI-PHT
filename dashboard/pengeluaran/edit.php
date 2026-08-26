<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
    exit;
}

$id = $_GET['id'];

$query = mysqli_query($conn,
"SELECT * FROM pengeluaran
WHERE id='$id'");

$data = mysqli_fetch_assoc($query);


// UPDATE DATA
if(isset($_POST['update'])){

    $nama = $_POST['nama_pengeluaran'];
    $nominal = str_replace(',', '', $_POST['nominal']);
    $keterangan = $_POST['keterangan'];
    $tanggal = $_POST['tanggal'];

    mysqli_query($conn,
    "UPDATE pengeluaran SET

            nama_pengeluaran='$nama',
            nominal='$nominal',
            tanggal='$tanggal',
            keterangan='$keterangan'

    WHERE id='$id'
    ");

    echo "
    <script>
        alert('Pengeluaran berhasil diupdate');
        window.location='index.php';
    </script>
    ";

}

?>

<!DOCTYPE html>
<html>
<head>

    <title>Edit Pengeluaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container py-5">

    <h2 class="mb-4">
        Edit Pengeluaran
    </h2>

    <form method="POST">

    <!-- Nama Pengeluaran -->
    <div class="mb-3">

        <label>Nama Pengeluaran</label>

        <input type="text"
               name="nama_pengeluaran"
               class="form-control"
               value="<?php echo htmlspecialchars($data['nama_pengeluaran']); ?>"
               required>

    </div>


    <!-- Nominal -->
    <div class="mb-3">

        <label>Nominal</label>

        <input type="text"
               name="nominal"
               id="nominal"
               class="form-control"
               value="<?php echo number_format($data['nominal']); ?>"
               inputmode="numeric"
               required>

    </div>


    <!-- Tanggal -->
    <div class="mb-3">

        <label>Tanggal</label>

        <input type="date"
               name="tanggal"
               class="form-control"
               value="<?php echo $data['tanggal']; ?>"
               required>

    </div>


    <!-- Keterangan -->
    <div class="mb-3">

        <label>Keterangan</label>

        <textarea name="keterangan"
                  class="form-control"
                  rows="4"
                  required><?php echo htmlspecialchars($data['keterangan']); ?></textarea>

    </div>


    <!-- Tombol -->
    <button type="submit"
            name="update"
            class="btn btn-success">

        Update

    </button>

    <a href="index.php"
       class="btn btn-secondary">

        Kembali

    </a>

</form>

</div>

<script>

const nominalInput = document.getElementById('nominal');

nominalInput.addEventListener('input', function() {

    let value = this.value.replace(/\D/g, '');

    if(value){
        value = Number(value).toLocaleString('en-US');
    }

    this.value = value;

});


document.querySelector('form').addEventListener('submit', function() {

    nominalInput.value = nominalInput.value.replace(/,/g, '');

});

</script>

</body>
</html>