<?php

session_start();

include '../../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT id FROM pengeluaran WHERE id = ? AND user_id = ?");
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

$stmtDel = mysqli_prepare($conn, "DELETE FROM pengeluaran WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmtDel, "ii", $id, $user_id);
$hapus = mysqli_stmt_execute($stmtDel);

if ($hapus) {
    echo "<script>
        alert('Data pengeluaran berhasil dihapus');
        window.location='index.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus data: " . addslashes(mysqli_error($conn)) . "');
        window.location='index.php';
    </script>";
}
