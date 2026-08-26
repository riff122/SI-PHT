<?php

session_start();
include '../../config/koneksi.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: ../../home.php");
    exit;
}


$id=$_GET['id'];


$data=mysqli_fetch_assoc(mysqli_query($conn,

"SELECT * FROM users WHERE id='$id'"

));


?>


<div class="container mt-4">

<h3>Detail Akun</h3>


<div class="card mt-3">

<div class="card-body">


<p>
<b>Nama :</b>
<?= $data['nama']; ?>
</p>



<p>
<b>Email :</b>
<?= $data['email']; ?>
</p>


<p>
<b>Role :</b>
<?= $data['role']; ?>
</p>



<p>
<b>Status :</b>

<?php if($data['status']=="aktif"){ ?>

<span class="badge bg-success">
Aktif
</span>

<?php }else{ ?>

<span class="badge bg-danger">
Tidak Aktif
</span>

<?php } ?>


</p>


<a href="index.php" class="btn btn-secondary">
Kembali
</a>


</div>

</div>

</div>