<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/koneksi.php';

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>
            alert('Email dan password wajib diisi!');
            window.location='../home.php';
        </script>";
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // cek email
    if ($data = mysqli_fetch_assoc($result)) {

        // cek password
        if (password_verify($password, $data['password'])) {

            // cek akun tidak aktif otomatis 6 bulan
            if ($data['last_login'] != null) {
                $cek_tanggal = strtotime($data['last_login']);
                $sekarang = time();
                $selisih = $sekarang - $cek_tanggal;

                // 6 bulan (180 hari)
                if ($selisih > (180 * 24 * 60 * 60)) {
                    $stmtDeact = mysqli_prepare($conn, "UPDATE users SET status='tidak_aktif' WHERE id = ?");
                    mysqli_stmt_bind_param($stmtDeact, "i", $data['id']);
                    mysqli_stmt_execute($stmtDeact);

                    echo "<script>
                        alert('Akun tidak aktif karena lama tidak digunakan. Silakan hubungi admin.');
                        window.location='../home.php';
                    </script>";
                    exit;
                }
            }

            // cek status akun
            if ($data['status'] == 'tidak_aktif') {
                echo "<script>
                    alert('Akun tidak aktif. Silakan hubungi admin.');
                    window.location='../home.php';
                </script>";
                exit;
            }

            // Update last login
            $stmtLast = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
            mysqli_stmt_bind_param($stmtLast, "i", $data['id']);
            mysqli_stmt_execute($stmtLast);

            $_SESSION['user'] = [
                'id' => $data['id'],
                'nama' => $data['nama'],
                'role' => $data['role']
            ];

            // redirect role
            if ($data['role'] == 'admin') {
                header("Location: ../dashboard/admin.php");
            } else {
                header("Location: ../dashboard/user.php");
            }
            exit;
        } else {
            echo "<script>
                alert('Password salah!');
                window.location='../home.php';
            </script>";
            exit;
        }
    } else {
        echo "<script>
            alert('Email tidak ditemukan!');
            window.location='../home.php';
        </script>";
        exit;
    }
} else {
    header("Location: ../home.php");
    exit;
}
