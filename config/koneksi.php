<?php

$host = "sql101.infinityfree.com";
$user = "if0_42838357";
$pass = "dSZN2h4pi4Fjv";
$db = "if0_42838357_ternakkk";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>