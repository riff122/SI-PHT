<?php

include '../config/koneksi.php';

if (isset($_POST['register'])) {

    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $wa    = trim($_POST['wa'] ?? '');

    $desa       = trim($_POST['desa'] ?? $_POST['dusun'] ?? '');
    $kelurahan  = trim($_POST['kelurahan'] ?? '');
    $kecamatan  = trim($_POST['kecamatan'] ?? 'Kemalang');
    $kabupaten  = trim($_POST['kabupaten'] ?? 'Klaten');

    $alamat = $desa . ", " . $kelurahan . ", " . $kecamatan . ", " . $kabupaten;

    $rawPassword     = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($email) || empty($wa) || empty($rawPassword)) {
        echo "<script>
            alert('Semua kolom wajib diisi!');
            window.location='../home.php';
        </script>";
        exit;
    }

    if (strlen($rawPassword) < 6) {
        echo "<script>
            alert('Password minimal terdiri dari 6 karakter!');
            window.location='../home.php';
        </script>";
        exit;
    }

    if (!empty($confirmPassword) && $rawPassword !== $confirmPassword) {
        echo "<script>
            alert('Konfirmasi password tidak cocok!');
            window.location='../home.php';
        </script>";
        exit;
    }

    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    // Cek apakah email sudah terdaftar menggunakan prepared statement
    $stmtCek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmtCek, "s", $email);
    mysqli_stmt_execute($stmtCek);
    $resCek = mysqli_stmt_get_result($stmtCek);

    if (mysqli_num_rows($resCek) > 0) {
        echo "<script>
            alert('Email sudah digunakan!');
            window.location='../home.php';
        </script>";
        exit;
    }

    // Insert user baru dengan prepared statement
    $role = 'peternak';
    $status = 'aktif';
    $stmtInsert = mysqli_prepare($conn, "
        INSERT INTO users (nama, email, wa, alamat, desa, password, role, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    mysqli_stmt_bind_param($stmtInsert, "ssssssss", $nama, $email, $wa, $alamat, $desa, $password, $role, $status);

    if (mysqli_stmt_execute($stmtInsert)) {
        echo "<script>
            alert('Registrasi berhasil! Silakan login.');
            window.location='../home.php';
        </script>";
    } else {
        echo "<script>
            alert('Terjadi kesalahan saat registrasi.');
            window.location='../home.php';
        </script>";
    }
}
