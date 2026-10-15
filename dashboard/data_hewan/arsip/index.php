<?php

session_start();

include '../../../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../../../home.php");
    exit;
}

if($_SESSION['user']['role'] != 'admin'){
    header("Location: ../../../home.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT
        hewan.*,
        users.nama AS peternak
    FROM hewan
    JOIN users
        ON hewan.user_id = users.id
    WHERE hewan.status_arsip = 1
    ORDER BY hewan.id DESC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Arsip Hewan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link rel="stylesheet" href="../../../style.css">

</head>

<body class="dashboard-admin">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                <i class="bi bi-archive"></i>
                Arsip Hewan
            </h2>

            <p class="arsip-deskripsi mb-0">
    Data hewan terjual yang telah diarsipkan
</p>

        </div>

        <a href="../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Foto</th>

                            <th>Nama Hewan</th>

                            <th>Jenis</th>

                            <th>Berat</th>

                            <th>Harga</th>

                            <th>Peternak</th>

                            <th>Status</th>

                            <th>Tanggal Terjual</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if(mysqli_num_rows($query) == 0){

                    ?>

                        <tr>

                            <td colspan="10"
                                class="text-center text-muted py-5">

                                <i class="bi bi-archive fs-1 d-block mb-3"></i>

                                Belum ada data hewan yang diarsipkan.

                            </td>

                        </tr>

                    <?php

                    }

                    while($data = mysqli_fetch_assoc($query)){

                    ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>


                            <td>

                                <?php if(!empty($data['foto'])){ ?>

                                    <img
                                        src="../../../images/hewan/<?= htmlspecialchars($data['foto']); ?>"
                                        width="70"
                                        height="70"
                                        style="object-fit:cover;border-radius:8px;">

                                <?php }else{ ?>

                                    -

                                <?php } ?>

                            </td>


                            <td>
                                <?= htmlspecialchars($data['nama_hewan']); ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($data['jenis']); ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($data['berat']); ?> Kg
                            </td>


                            <td>
                                Rp <?= number_format(
                                    $data['harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($data['peternak']); ?>
                            </td>


                            <td>

                                <span class="badge bg-danger">
                                    Terjual
                                </span>

                                <span class="badge bg-secondary">
                                    Diarsipkan
                                </span>

                            </td>


                            <td>

                                <?php

                                if(!empty($data['tanggal_terjual'])){

                                    echo date(
                                        'd-m-Y H:i',
                                        strtotime($data['tanggal_terjual'])
                                    );

                                }else{

                                    echo "-";

                                }

                                ?>

                            </td>


                            <td>

                                <a
                                    href="../detail.php?id=<?= $data['id']; ?>"
                                    class="btn btn-info btn-sm">

                                    <i class="bi bi-eye"></i>
                                    Detail

                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>