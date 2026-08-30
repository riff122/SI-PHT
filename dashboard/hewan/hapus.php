<?php

session_start();

include '../../config/koneksi.php';

// Verifikasi sesi login
if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifikasi kepemilikan data hewan oleh peternak yang sedang login
$stmt = mysqli_prepare($conn, "SELECT * FROM hewan WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan atau bukan milik peternak yang login
if (!$data) {
    echo "<script>
        alert('Data tidak ditemukan atau Anda tidak memiliki akses.');
        window.location='index.php';
    </script>";
    exit;
}

// 1. Hapus foto utama jika ada
if (!empty($data['foto'])) {
    $pathUtama = "../../images/hewan/" . $data['foto'];
    if (file_exists($pathUtama)) {
        unlink($pathUtama);
    }
}

// 2. Ambil dan hapus semua foto pendukung dari tabel hewan_foto
$stmtFotos = mysqli_prepare($conn, "SELECT foto FROM hewan_foto WHERE hewan_id = ?");
mysqli_stmt_bind_param($stmtFotos, "i", $id);
mysqli_stmt_execute($stmtFotos);
$resFotos = mysqli_stmt_get_result($stmtFotos);

while ($f = mysqli_fetch_assoc($resFotos)) {
    if (!empty($f['foto'])) {
        $pathGaleri = "../../images/hewan/" . $f['foto'];
        if (file_exists($pathGaleri)) {
            unlink($pathGaleri);
        }
    }
}

// 3. Hapus relasi di hewan_foto dan pengeluaran
$stmtDelFoto = mysqli_prepare($conn, "DELETE FROM hewan_foto WHERE hewan_id = ?");
mysqli_stmt_bind_param($stmtDelFoto, "i", $id);
mysqli_stmt_execute($stmtDelFoto);

$stmtDelPengeluaran = mysqli_prepare($conn, "DELETE FROM pengeluaran WHERE hewan_id = ?");
mysqli_stmt_bind_param($stmtDelPengeluaran, "i", $id);
mysqli_stmt_execute($stmtDelPengeluaran);

// 4. Hapus data hewan
$stmtDelHewan = mysqli_prepare($conn, "DELETE FROM hewan WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmtDelHewan, "ii", $id, $user_id);
$hapus = mysqli_stmt_execute($stmtDelHewan);

if ($hapus) {
    echo "<script>
        alert('Data hewan beserta foto dan pengeluaran berhasil dihapus');
        window.location='index.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus data: " . addslashes(mysqli_error($conn)) . "');
        window.location='index.php';
    </script>";
}
