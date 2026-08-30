<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

if (isset($_POST['submit'])) {

    $user_id = (int)$_SESSION['user']['id'];

    $nama = trim($_POST['nama_hewan'] ?? '');
    $ras = trim($_POST['ras'] ?? '');
    $jenis = trim($_POST['jenis'] ?? '');
    $berat = (float)str_replace(',', '.', $_POST['berat'] ?? '0');
    $satuan_berat = trim($_POST['satuan_berat'] ?? 'kg');
    $umur = trim($_POST['umur'] ?? '');
    $harga = (int)str_replace('.', '', $_POST['harga'] ?? '0');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = trim($_POST['status'] ?? 'Tersedia');

    // Cek jumlah foto
    if (!isset($_FILES['foto']) || empty($_FILES['foto']['name'][0])) {
        echo "<script>
            alert('Silakan pilih minimal 4 foto.');
            window.history.back();
        </script>";
        exit;
    }

    $jumlahFoto = count($_FILES['foto']['name']);

    if ($jumlahFoto < 4) {
        echo "<script>
            alert('Minimal upload 4 foto hewan');
            window.history.back();
        </script>";
        exit;
    }

    if ($jumlahFoto > 6) {
        echo "<script>
            alert('Maksimal upload 6 foto hewan');
            window.history.back();
        </script>";
        exit;
    }

    // Validasi ekstensi semua foto sebelum upload
    $ekstensiOk = ['jpg', 'jpeg', 'png', 'webp'];
    for ($i = 0; $i < $jumlahFoto; $i++) {
        $namaFoto = $_FILES['foto']['name'][$i];
        $ext = strtolower(pathinfo($namaFoto, PATHINFO_EXTENSION));
        if (!in_array($ext, $ekstensiOk)) {
            echo "<script>
                alert('Format foto tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
                window.history.back();
            </script>";
            exit;
        }
    }

    // Pastikan folder upload ada
    $uploadDir = "../../images/hewan/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Insert data hewan menggunakan Prepared Statement
    $stmt = mysqli_prepare($conn, "
        INSERT INTO hewan (
            user_id, nama_hewan, ras, jenis, berat, satuan_berat, umur, harga, deskripsi, latitude, longitude, foto, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, NOW(), NOW()
        )
    ");
    mysqli_stmt_bind_param(
        $stmt,
        "isssdssissss",
        $user_id,
        $nama,
        $ras,
        $jenis,
        $berat,
        $satuan_berat,
        $umur,
        $harga,
        $deskripsi,
        $latitude,
        $longitude,
        $status
    );

    if (mysqli_stmt_execute($stmt)) {
        $id_hewan = mysqli_insert_id($conn);
        $fotoPertama = "";

        // Upload semua foto dan insert ke hewan_foto
        $stmtFoto = mysqli_prepare($conn, "INSERT INTO hewan_foto (hewan_id, foto) VALUES (?, ?)");

        for ($i = 0; $i < $jumlahFoto; $i++) {
            $namaFoto = $_FILES['foto']['name'][$i];
            $tmpFoto = $_FILES['foto']['tmp_name'][$i];
            $ext = strtolower(pathinfo($namaFoto, PATHINFO_EXTENSION));

            $namaBaru = time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (move_uploaded_file($tmpFoto, $uploadDir . $namaBaru)) {
                if ($i === 0) {
                    $fotoPertama = $namaBaru;
                }

                mysqli_stmt_bind_param($stmtFoto, "is", $id_hewan, $namaBaru);
                mysqli_stmt_execute($stmtFoto);
            }
        }

        // Update foto utama untuk marketplace
        if (!empty($fotoPertama)) {
            $stmtUpdateFoto = mysqli_prepare($conn, "UPDATE hewan SET foto = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmtUpdateFoto, "si", $fotoPertama, $id_hewan);
            mysqli_stmt_execute($stmtUpdateFoto);
        }

        echo "
        <script>
        let pilihan = confirm(
            '✅ Data hewan berhasil disimpan.\\n\\n' +
            'Sebelum hewan ditampilkan di Marketplace, apakah Anda ingin menambahkan data pengeluaran terlebih dahulu?\\n\\n' +
            'OK = Tambah Pengeluaran\\n' +
            'Cancel = Langsung Tampilkan ke Marketplace'
        );

        if (pilihan) {
            window.location='../pengeluaran/tambah.php?hewan_id=" . $id_hewan . "';
        } else {
            alert('Hewan akan langsung ditampilkan di Marketplace.');
            window.location='../../home.php#marketplace';
        }
        </script>
        ";
        exit;
    } else {
        echo "<script>
            alert('Gagal menyimpan data hewan: " . addslashes(mysqli_error($conn)) . "');
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
    <title>Tambah Hewan - Dashboard Peternak</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="bg-light">

<div class="container py-5" style="max-width: 800px;">

    <div class="d-flex align-items-center justify-content-between mb-4 p-4 bg-white rounded shadow-sm">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Tambah Hewan</h3>
            <small class="text-muted">Input data dan foto hewan ternak baru</small>
        </div>

        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            ← Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm p-4">
        <form method="POST" enctype="multipart/form-data">

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <!-- Nama Hewan -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama / Kode Hewan</label>
                <input type="text"
                       name="nama_hewan"
                       class="form-control"
                       placeholder="Contoh: Sapi Limousin Super A"
                       required>
            </div>

            <!-- Kategori Hewan -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Kategori Hewan</label>
                <select name="jenis_hewan" id="jenis_hewan" class="form-select" required>
                    <option value="">Pilih Kategori Hewan</option>
                    <option value="Sapi">Sapi</option>
                    <option value="Kambing">Kambing</option>
                    <option value="Domba">Domba</option>
                    <option value="Ayam">Ayam</option>
                    <option value="Burung">Burung</option>
                </select>
            </div>

            <!-- Ras -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Ras</label>
                <select name="ras" id="ras" class="form-select" required disabled>
                    <option value="">Pilih kategori hewan terlebih dahulu</option>
                </select>
            </div>

            <!-- Jenis Kelamin -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Kelamin</label>
                <select name="jenis" class="form-select">
                    <option value="Jantan">Jantan</option>
                    <option value="Betina">Betina</option>
                </select>
            </div>

            <!-- Berat -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Berat</label>
                <div class="input-group">
                    <input type="text"
                           name="berat"
                           id="berat"
                           class="form-control"
                           inputmode="decimal"
                           placeholder="Contoh: 0,8 atau 350"
                           required>
                    <select name="satuan_berat" class="form-select" style="max-width: 120px;">
                        <option value="kg">Kg</option>
                        <option value="gram">Gram</option>
                    </select>
                </div>
                <small class="text-muted">Contoh: 350 kg atau 750 gram</small>
            </div>

            <!-- Umur -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Umur</label>
                <input type="text"
                       name="umur"
                       class="form-control"
                       placeholder="Contoh: 2 tahun 3 bulan"
                       required>
            </div>

            <!-- Harga -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Harga Jual (Rp)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text"
                           id="harga"
                           name="harga"
                           class="form-control"
                           placeholder="Contoh: 22.000.000"
                           inputmode="numeric"
                           required>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi / Keterangan Hewan</label>
                <textarea name="deskripsi"
                          class="form-control"
                          rows="3"
                          placeholder="Jelaskan kondisi kesehatan, pakan, vaksinasi, dll."></textarea>
            </div>

            <!-- Lokasi GPS -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Lokasi Peternakan (GPS)</label>
                <button type="button"
                        onclick="ambilLokasi()"
                        class="btn btn-outline-primary btn-sm d-block mb-2">
                    📍 Ambil Lokasi Saya
                </button>

                <iframe id="mapPreview"
                        width="100%"
                        height="250"
                        style="border: 0; border-radius: 12px; display: none;"
                        loading="lazy"></iframe>
                <small class="text-muted">Klik tombol untuk mengambil koordinat lokasi kandang otomatis via GPS.</small>
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Status Ketersediaan</label>
                <select name="status" class="form-select">
                    <option value="Tersedia">Tersedia</option>
                    <option value="Terjual">Terjual</option>
                </select>
            </div>

            <!-- Foto Upload -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Foto Hewan (Minimal 4 - Maksimal 6)</label>
                <input type="file"
                       id="foto"
                       name="foto[]"
                       class="form-control"
                       multiple
                       accept="image/*">

                <div class="mt-3">
                    <h6 class="fw-semibold">Foto yang dipilih:</h6>
                    <div id="previewFoto" class="d-flex gap-3 flex-wrap"></div>
                    <p id="statusFoto" class="mt-2 small text-muted">Belum ada foto yang dipilih</p>
                </div>
            </div>

            <button type="submit"
                    name="submit"
                    class="btn btn-success px-4 py-2 fw-semibold">
                Simpan & Posting Hewan
            </button>

        </form>
    </div>

</div>

<script>
const jenisHewan = document.getElementById('jenis_hewan');
const ras = document.getElementById('ras');

const daftarRas = {
    Sapi: ["Limousin", "Simmental", "PO", "Bali", "Brahman", "Madura", "Lainnya"],
    Kambing: ["Jawa", "Etawa", "Kacang", "Boer", "Jawarandu", "Lainnya"],
    Domba: ["Garut", "Merino", "Texel", "Domba Lokal", "Lainnya"],
    Ayam: ["Kampung", "Broiler", "Bangkok", "Cemani", "Joper", "Petelur", "Lainnya"],
    Burung: ["Kenari", "Kenari Yorkshire", "Lovebird", "Murai Batu", "Kacer", "Cucak Ijo", "Pleci", "Perkutut", "Jalak", "Lainnya"]
};

jenisHewan.addEventListener('change', function() {
    const jenis = this.value;
    ras.innerHTML = "";

    if (!jenis) {
        ras.disabled = true;
        ras.innerHTML = '<option value="">Pilih kategori hewan terlebih dahulu</option>';
        return;
    }

    ras.disabled = false;
    ras.innerHTML = '<option value="">Pilih Ras</option>';
    daftarRas[jenis].forEach(function(namaRas) {
        const option = document.createElement('option');
        option.value = namaRas;
        option.textContent = namaRas;
        ras.appendChild(option);
    });
});

document.getElementById('berat').addEventListener('input', function() {
    let value = this.value.replace(/[^0-9,]/g, '');
    let bagian = value.split(',');
    if (bagian.length > 2) {
        value = bagian[0] + ',' + bagian.slice(1).join('');
    }
    this.value = value;
});

const inputHarga = document.getElementById('harga');
inputHarga.addEventListener('input', function () {
    let angka = this.value.replace(/\D/g, '');
    if (angka === '') {
        this.value = '';
        return;
    }
    this.value = new Intl.NumberFormat('id-ID').format(angka);
});

function ambilLokasi() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                let map = document.getElementById('mapPreview');
                map.style.display = "block";
                map.src = "https://www.google.com/maps?q=" + lat + "," + lng + "&output=embed";

                alert("Lokasi berhasil diambil\nAkurasi sekitar: " + Math.round(position.coords.accuracy) + " meter");
            },
            function() {
                alert("Lokasi gagal diambil. Pastikan GPS aktif.");
            },
            {
                enableHighAccuracy: true,
                timeout: 60000,
                maximumAge: 0
            }
        );
    } else {
        alert("Browser tidak mendukung GPS");
    }
}

let daftarFoto = [];
const inputFoto = document.getElementById('foto');

inputFoto.addEventListener('change', function(e) {
    let fileBaru = Array.from(e.target.files);
    fileBaru.forEach(file => {
        if (daftarFoto.length < 6) {
            daftarFoto.push(file);
        }
    });
    inputFoto.value = "";
    tampilkanFoto();
});

function tampilkanFoto() {
    let preview = document.getElementById('previewFoto');
    preview.innerHTML = "";

    daftarFoto.forEach((file, index) => {
        let reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML += `
                <div class="card shadow-sm" style="width: 140px; flex-shrink: 0;">
                    <img src="${e.target.result}" style="width: 140px; height: 110px; object-fit: cover; border-radius: 8px;">
                    <div class="p-2 text-center">
                        <small class="d-block mb-1">Foto ${index + 1}</small>
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="hapusFoto(${index})">Hapus</button>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    });

    let jumlah = daftarFoto.length;
    let statusFoto = document.getElementById('statusFoto');
    if (jumlah < 4) {
        statusFoto.innerHTML = "⚠️ Minimal 4 foto (" + jumlah + "/6)";
        statusFoto.className = "mt-2 small text-danger";
    } else {
        statusFoto.innerHTML = "✅ Foto memenuhi syarat (" + jumlah + "/6)";
        statusFoto.className = "mt-2 small text-success";
    }
}

function hapusFoto(index) {
    daftarFoto.splice(index, 1);
    tampilkanFoto();
}

document.querySelector('form').addEventListener('submit', function(e) {
    let dataTransfer = new DataTransfer();
    daftarFoto.forEach(file => {
        dataTransfer.items.add(file);
    });
    inputFoto.files = dataTransfer.files;
});
</script>

</body>
</html>