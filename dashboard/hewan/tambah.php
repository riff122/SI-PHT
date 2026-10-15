<?php

session_start();

include '../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../index.php");
    exit;
}

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user']['id'];

    $nama = $_POST['nama_hewan'];
    $ras = $_POST['ras'];
    $jenis = $_POST['jenis'];
    $berat = str_replace(',', '.', $_POST['berat']);
    $berat = (float) $berat;
    $satuan_berat = $_POST['satuan_berat'];
    $umur = $_POST['umur'];
    $harga = str_replace('.', '', $_POST['harga']);
    $harga = (int) $harga;
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Ambil dukuh peternak dari tabel users
    $user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT desa FROM users WHERE id='$user_id'"));

    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];

    // CEK JUMLAH FOTO

if (
    !isset($_FILES['foto']) ||
    empty($_FILES['foto']['name'][0])
) {
    echo "
    <script>
    alert('Silakan pilih minimal 4 foto.');
    window.history.back();
    </script>
    ";
    exit;
}

$jumlahFoto = count($_FILES['foto']['name']);


if($jumlahFoto < 4){

    echo "
    <script>
    alert('Minimal upload 4 foto hewan');
    window.history.back();
    </script>
    ";

    exit;

}


if($jumlahFoto > 6){

    echo "
    <script>
    alert('Maksimal upload 6 foto hewan');
    window.history.back();
    </script>
    ";

    exit;

}


// FOTO PERTAMA UNTUK MARKETPLACE


$ext = strtolower(
    pathinfo($namaFotoUtama, PATHINFO_EXTENSION)
);




    $insert = mysqli_query($conn,
    "INSERT INTO hewan(
        user_id,
        nama_hewan,
        ras,
        jenis,
        berat,
        satuan_berat,
        umur,
        harga,
        deskripsi,
        latitude,
        longitude,
        foto,
        status
        
    ) VALUES(
        '$user_id',
        '$nama',
        '$ras',
        '$jenis',
        '$berat',
        '$satuan_berat',
        '$umur',
        '$harga',
        '$deskripsi',
        '$latitude', 
        '$longitude',
        '',
        '$status'
    )");

    if($insert){

    $id_hewan = mysqli_insert_id($conn);

// SIMPAN SEMUA FOTO

$fotoPertama = "";


for($i=0;$i<$jumlahFoto;$i++){

    $namaFoto = $_FILES['foto']['name'][$i];

    $tmpFoto = $_FILES['foto']['tmp_name'][$i];


    $namaBaru = time().'_'.$i.'_'.$namaFoto;


    move_uploaded_file(
        $tmpFoto,
        "../../images/hewan/".$namaBaru
    );


    // foto pertama untuk marketplace
    if($i==0){

        $fotoPertama = $namaBaru;

    }


    mysqli_query($conn,"
    INSERT INTO hewan_foto
    (hewan_id,foto)

    VALUES
    ('$id_hewan','$namaBaru')
    ");

}


// update foto utama marketplace

mysqli_query($conn,"
UPDATE hewan
SET foto='$fotoPertama'
WHERE id='$id_hewan'
");

    echo "
<script>

let pilihan = confirm(
'✅ Data hewan berhasil disimpan.\\n\\n' +
'Sebelum hewan ditampilkan di Marketplace, apakah Anda ingin menambahkan data pengeluaran terlebih dahulu?\\n\\n' +
'OK = Tambah Pengeluaran\\n' +
'Cancel = Langsung Tampilkan ke Marketplace'
);

if(pilihan){

    window.location='../pengeluaran/tambah.php?hewan_id=".$id_hewan."';

}else{

    alert(
        'Hewan akan langsung ditampilkan di Marketplace.'
    );

    window.location='../../home.php#marketplace';

}

</script>
";

}

}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Hewan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container py-5">

    <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded shadow-sm nav-box">

    <!-- KIRI: NAVIGASI + JUDUL -->
    <div class="d-flex align-items-center gap-2">

        <a href="index.php" class="btn btn-outline-secondary btn-sm nav-btn">
            ← 
        </a>

        <div class="ms-3">
            <h3 class="mb-0 fw-bold">
                Tambah Hewan
            </h3>
            <small class="text-muted">Input data hewan ternak baru</small>
        </div>

    </div>

</div>

    <form method="POST" enctype="multipart/form-data">

    <input type="hidden" name="latitude" id="latitude">

    <input type="hidden" name="longitude" id="longitude">

        <div class="mb-3">
            <label>Nama Hewan</label>

            <input type="text" 
             name="nama_hewan" 
             class="form-control" 
             required>
        </div>

<div class="mb-3">
    <label>Jenis Hewan</label>

    <select name="jenis_hewan" id="jenis_hewan" class="form-select" required>
        <option value="">Pilih Jenis Hewan</option>
        <option value="Sapi">Sapi</option>
        <option value="Kambing">Kambing</option>
        <option value="Domba">Domba</option>
        <option value="Ayam">Ayam</option>
        <option value="Burung">Burung</option>
    </select>
</div>

<div class="mb-3">
    <label>Ras</label>

    <select name="ras" id="ras" class="form-select" required disabled>
        <option value="">Pilih jenis hewan terlebih dahulu</option>
    </select>
</div>

        <div class="mb-3">

            <label>Jenis</label>

            <select name="jenis" class="form-select">

                <option>Jantan</option>
                <option>Betina</option>
                

            </select>

        </div>

<div class="mb-3">
    <label>Berat</label>

    <div class="input-group">
        <input type="text"
               name="berat"
               id="berat"
               class="form-control"
               inputmode="decimal"
               placeholder="Contoh: 0,8"
               required>

        <select name="satuan_berat" class="form-select" style="max-width: 120px;">
            <option value="kg">Kg</option>
            <option value="gram">Gram</option>
        </select>
    </div>

    <small class="text-muted">
        Contoh: 0,8 kg atau 750 gram
    </small>
</div>

        <div class="mb-3">
    <label>Umur</label>

    <input type="text"
           name="umur"
           class="form-control"
           placeholder="Contoh: 1 tahun 6 bulan"
           required>

    <small class="text-muted">
        Masukkan umur lengkap beserta satuannya.
    </small>
</div>

<div class="mb-3">
    <label>Harga</label>

    <div class="input-group">
        <span class="input-group-text">Rp</span>

        <input type="text"
               id="harga"
               name="harga"
               class="form-control"
               placeholder="Contoh: 3.000.000"
               inputmode="numeric"
               required>
    </div>

    <small class="text-muted">
        Masukkan harga tanpa mengetik titik secara manual.
    </small>
</div>

        <div class="mb-3">

            <label>Deskripsi</label>

            <textarea name="deskripsi"
                      class="form-control"></textarea>

        </div>

        <div class="mb-3">

<label>Lokasi Peternakan</label>

<button type="button"
onclick="ambilLokasi()"
class="btn btn-primary">

📍 Ambil Lokasi Saya

</button>

<div class="mt-3">

<iframe
id="mapPreview"
width="100%"
height="300"
style="border:0; border-radius:15px; display:none;"
loading="lazy">
</iframe>

</div>

<small class="d-block text-muted mt-2">
Klik tombol untuk mengambil lokasi kandang otomatis
</small>

</div>

        <!-- STATUS -->
        <div class="mb-3">

            <label>Status</label>

            <select name="status" class="form-select">

                <option value="Tersedia">
                    Tersedia
                </option>

                <option value="Terjual">
                    Terjual
                </option>

            </select>

        </div>

        <!-- FOTO -->
        <div class="mb-3">

<label>Foto Hewan (Minimal 4 - Maksimal 6)</label>

<input type="file"
id="foto"
name="foto[]"
class="form-control"
multiple
accept="image/*">


<!-- TRACK RECORD FOTO -->
<div class="mt-3">

<h6 class="fw-bold">
Foto yang dipilih:
</h6>


<div id="previewFoto" 
class="d-flex gap-3 flex-wrap">

</div>

<p id="statusFoto" class="mt-2 text-muted">

Belum ada foto

</p>


</div>


</div>

        <button type="submit"
                name="submit"
                class="btn btn-success"
                onclick="return konfirmasiJual()">

            Simpan

        </button>

    </form>

</div>

<script>
function konfirmasiJual(){
    return confirm(
        "Verifikasi Penjualan\n\n" +
        "Hewan ini akan ditampilkan di Marketplace dan dapat dilihat oleh calon pembeli.\n\n" +
        "Apakah Anda yakin ingin melanjutkan?"
    );
}
</script>

<script>

const jenisHewan = document.getElementById('jenis_hewan');
const ras = document.getElementById('ras');

const daftarRas = {

    Sapi: [
        "Limousin",
        "Simmental",
        "PO",
        "Bali",
        "Brahman",
        "Madura",
        "Lainnya"
    ],

    Kambing: [
        "Jawa",
        "Etawa",
        "Kacang",
        "Boer",
        "Jawarandu",
        "Lainnya"
    ],

    Domba: [
        "Garut",
        "Merino",
        "Texel",
        "Domba Lokal",
        "Lainnya"
    ],

    Ayam: [
        "Kampung",
        "Broiler",
        "Bangkok",
        "Cemani",
        "Joper",
        "Petelur",
        "Lainnya"
    ],

    Burung: [
        "Kenari",
        "Kenari Yorkshire",
        "Lovebird",
        "Murai Batu",
        "Kacer",
        "Cucak Ijo",
        "Pleci",
        "Perkutut",
        "Jalak",
        "Lainnya"
    ]

};

jenisHewan.addEventListener('change', function(){

    const jenis = this.value;

    ras.innerHTML = "";

    if(!jenis){

        ras.disabled = true;

        ras.innerHTML =
            '<option value="">Pilih jenis hewan terlebih dahulu</option>';

        return;
    }

    ras.disabled = false;

    ras.innerHTML =
        '<option value="">Pilih Ras</option>';

    daftarRas[jenis].forEach(function(namaRas){

        const option = document.createElement('option');

        option.value = namaRas;
        option.textContent = namaRas;

        ras.appendChild(option);

    });

});

</script>

<script>

document.getElementById('berat').addEventListener('input', function(){

    let value = this.value;

    value = value.replace(/[^0-9,]/g, '');

    let bagian = value.split(',');

    if(bagian.length > 2){

        value = bagian[0] + ',' + bagian.slice(1).join('');

    }

    this.value = value;

});

</script>

<script>

function ambilLokasi(){

    if(navigator.geolocation){

        navigator.geolocation.getCurrentPosition(
        
        function(position){

            document.getElementById('latitude').value =
            position.coords.latitude;

            document.getElementById('longitude').value =
            position.coords.longitude;


            let akurasi = Math.round(position.coords.accuracy);


if(akurasi > 1000){

    alert(
    "Lokasi kurang akurat.\n\n" +
    "Akurasi saat ini: "+akurasi+" meter\n\n" +
    "Silakan tekan Ambil Lokasi lagi di tempat terbuka."
    );

    return;

}else{

    alert(
    "Lokasi berhasil diambil.\n\n" +
    "Akurasi: "+akurasi+" meter"
    );

}

        },

        function(error){

            alert("Lokasi gagal diambil. Pastikan GPS aktif.");

        },

        {
            enableHighAccuracy: true,
            timeout: 60000,
            maximumAge: 0
        }

        );

    }else{

        alert("Browser tidak mendukung GPS");

    }

}

</script>

<script>
function konfirmasiJual(){
...
}
</script>


<script>


let daftarFoto = [];

const inputFoto = document.getElementById('foto');


inputFoto.addEventListener('change', function(e){


    let fileBaru = Array.from(e.target.files);


    fileBaru.forEach(file=>{

        if(daftarFoto.length < 6){

            daftarFoto.push(file);

        }

    });


    // kosongkan input agar bisa upload lagi
    inputFoto.value="";


    tampilkanFoto();

});



function tampilkanFoto(){


let preview = document.getElementById('previewFoto');

preview.innerHTML="";


daftarFoto.forEach((file,index)=>{


let reader = new FileReader();


reader.onload=function(e){


preview.innerHTML += `

<div class="card shadow-sm"
style="
width:150px;
flex-shrink:0;
">


<img src="${e.target.result}"
style="
width:150px;
height:120px;
object-fit:cover;
border-radius:10px;
">


<div class="p-2">


<small>
Foto ${index+1}
</small>


<button type="button"
class="btn btn-danger btn-sm w-100 mt-2"
onclick="hapusFoto(${index})">

Hapus

</button>


</div>


</div>

`;


}


reader.readAsDataURL(file);



});



let jumlah = daftarFoto.length;


let statusFoto=document.getElementById('statusFoto');


if(jumlah < 4){


statusFoto.innerHTML =
"⚠️ Minimal 4 foto ("+jumlah+"/6)";


statusFoto.className="text-danger";


}else{


statusFoto.innerHTML =
"✅ Foto memenuhi syarat ("+jumlah+"/6)";


statusFoto.className="text-success";


}


}




function hapusFoto(index){


daftarFoto.splice(index,1);


tampilkanFoto();


}



// sebelum submit masukkan foto ke form
document.querySelector('form').addEventListener('submit',function(e){


let dataTransfer = new DataTransfer();


daftarFoto.forEach(file=>{

    dataTransfer.items.add(file);

});


inputFoto.files = dataTransfer.files;



});


</script>
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