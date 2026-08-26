<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){

    header("Location: ../../home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$hewan_id = (int)$_GET['hewan_id'];

// cek hewan milik user
$cek = mysqli_query($conn,
"SELECT * FROM hewan
WHERE id='$hewan_id'
AND user_id='$user_id'");

$hewan = mysqli_fetch_assoc($cek);

if(!$hewan){

    echo "
    <script>
        alert('Data hewan tidak ditemukan');
        window.location='../hewan/index.php';
    </script>
    ";

    exit;
}

// simpan
if(isset($_POST['submit'])){

    if(
    empty($_POST['nama_pengeluaran']) ||
    empty($_POST['nominal']) ||
    empty($_POST['tanggal']) ||
    empty($_POST['keterangan'])
){
    echo "
    <script>
        alert('Semua field wajib diisi!');
        window.history.back();
    </script>
    ";
    exit;
}

    $nama = $_POST['nama_pengeluaran'];
    $nominal = $_POST['nominal'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];

    $insert = mysqli_query($conn,
    "INSERT INTO pengeluaran(

        hewan_id,
        user_id,
        nama_pengeluaran,
        nominal,
        tanggal,
        keterangan

    ) VALUES(

        '$hewan_id',
        '$user_id',
        '$nama',
        '$nominal',
        '$tanggal',
        '$keterangan'
    )");

    if($insert){

        echo "
        <script>
            alert('Pengeluaran berhasil ditambahkan');
            window.location='index.php';
        </script>
        ";
        }else{

            die(mysqli_error($conn));

    }

}



?>

<!DOCTYPE html>
<html>
<head>

    <title>Tambah Pengeluaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    background: #f8f9fa;
    color: #212529;
    font-family: Arial, sans-serif;
}

.form-wrapper {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 40px 20px;
}

.form-box {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 30px;
}

.form-title {
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #212529;
    text-align: center;
}

.form-subtitle {
    color: #6c757d;
    margin-bottom: 30px;
    text-align: center;
}

label {
    margin-bottom: 8px;
    font-weight: 500;
    color: #212529;
}

.form-control {
    background: #ffffff !important;
    border: 1px solid #ced4da !important;
    color: #212529 !important;
    padding: 10px 12px;
    border-radius: 6px;
}

.form-control:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .15) !important;
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
                    Catat seluruh kebutuhan ternak Anda dengan rapi
                </p>

                <form method="POST">

                    <div class="mb-4">

                        <label>Nama Pengeluaran</label>

                        <input type="text"
                        name="nama_pengeluaran"
                        class="form-control"
                        placeholder="Contoh: Pakan, Vitamin, Obat"
                        required>
                    </div>

                    <div class="mb-4">

    <label>Nominal</label>

    <input type="text"
    name="nominal"
    id="nominal"
    class="form-control"
    placeholder="Contoh: 1,000,000"
    inputmode="numeric"
    required>
    
</div>
                    <div class="mb-4">

                        <label>Tanggal</label>

                        <input type="date"
                        name="tanggal"
                        class="form-control"
                        required>
                    </div>

                    <div class="mb-4">

                        <label>Keterangan</label>

                        <textarea
                        class="form-control"
                        rows="5"
                        name="keterangan"
                        required
                        placeholder="Tambahkan catatan"></textarea>
                        
                    </div>

                   <button type="submit"
                        name="submit"
                        class="btn btn-success">

                        Simpan

                    </button>

                    <a href="index.php?hewan_id=<?php echo $hewan_id; ?>"
                    class="btn btn-secondary">

                        Kembali

                    </a>


                </form>

            </div>

        </div>
        </div>

    </div>

</body>

</div>
</div>

<script>
const nominalInput = document.getElementById('nominal');

nominalInput.addEventListener('input', function() {

    // Ambil hanya angka
    let value = this.value.replace(/\D/g, '');

    // Format menggunakan koma sebagai pemisah ribuan
    if(value){
        value = Number(value).toLocaleString('en-US');
    }

    this.value = value;
});

// Sebelum dikirim, hapus koma
document.querySelector('form').addEventListener('submit', function() {

    nominalInput.value = nominalInput.value.replace(/,/g, '');

});
</script>

</body>
</html>