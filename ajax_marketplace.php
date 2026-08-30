<?php

include 'config/koneksi.php';

$limit = 9; // 9 hewan per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$start = ($page - 1) * $limit;

$where = "WHERE hewan.status_arsip = 0";

// FILTER DUSUN / LOKASI
if (!empty($_GET['lokasi'])) {
    $lokasi = mysqli_real_escape_string($conn, trim($_GET['lokasi']));
    $where .= " AND users.alamat LIKE '%$lokasi%'";
}

// FILTER HEWAN
if (!empty($_GET['hewan'])) {
    $hewan = mysqli_real_escape_string($conn, trim($_GET['hewan']));
    $where .= " AND hewan.nama_hewan LIKE '%$hewan%'";
}

// FILTER RAS
if (!empty($_GET['ras'])) {
    $ras = mysqli_real_escape_string($conn, trim($_GET['ras']));
    $where .= " AND hewan.ras = '$ras'";
}

// FILTER PETERNAK
if (!empty($_GET['peternak'])) {
    $peternak = (int)$_GET['peternak'];
    $where .= " AND hewan.user_id = '$peternak'";
}

// HITUNG TOTAL DATA
$count = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM hewan
    JOIN users ON hewan.user_id = users.id
    $where
");

$totalData = (int)(mysqli_fetch_assoc($count)['total'] ?? 0);
$totalPage = ceil($totalData / $limit);

// AMBIL DATA SESUAI HALAMAN
$query = mysqli_query($conn, "
    SELECT 
        hewan.*, 
        users.nama,
        users.wa,
        users.alamat
    FROM hewan
    JOIN users ON hewan.user_id = users.id
    $where
    ORDER BY hewan.updated_at DESC
    LIMIT $start, $limit
");

if (mysqli_num_rows($query) === 0) {
    echo "
    <div class='col-12 text-center py-5'>
        <div class='alert alert-warning border-0 shadow-sm d-inline-block px-4 py-3 rounded-4'>
            <i class='bi bi-search me-2 fs-5'></i> Hewan ternak tidak ditemukan dengan kriteria pencarian ini.
        </div>
    </div>
    ";
}

while ($data = mysqli_fetch_assoc($query)) {
    $fotoPath = (!empty($data['foto']) && file_exists('images/hewan/' . $data['foto']))
        ? 'images/hewan/' . htmlspecialchars($data['foto'])
        : 'images/default.jpg';
    $isTerjual = ($data['status'] === 'Terjual');
    $formattedBerat = rtrim(rtrim(number_format($data['berat'], 2, ',', '.'), '0'), ',');
    $formattedHarga = number_format($data['harga'], 0, ',', '.');
    $waClean = ltrim($data['wa'], '0');
    ?>
    <div class="col-lg-4 col-md-6 card-fade-in">
        <div class="card animal-card h-100 <?= $isTerjual ? 'opacity-50' : ''; ?>">
            <div class="position-relative">
                <img src="<?= $fotoPath; ?>"
                     class="card-img-top"
                     style="height:250px; object-fit:cover;"
                     alt="<?= htmlspecialchars($data['nama_hewan']); ?>">

                <?php if ($isTerjual): ?>
                    <div class="sold-label">TERJUAL</div>
                <?php endif; ?>
            </div>

            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge <?= !$isTerjual ? 'bg-success' : 'bg-danger'; ?>">
                        <?= htmlspecialchars($data['status']); ?>
                    </span>
                    <span class="badge-weight">
                        <?= $formattedBerat . ' ' . htmlspecialchars($data['satuan_berat']); ?>
                    </span>
                </div>

                <small class="text-muted">
                    📍 <?= htmlspecialchars($data['alamat']); ?>
                </small>

                <h4 class="fw-bold mt-3">
                    <?= htmlspecialchars($data['nama_hewan']); ?>
                </h4>

                <p class="text-success mb-1">
                    <strong>Ras :</strong> <?= htmlspecialchars($data['ras']); ?>
                </p>

                <p class="text-muted mb-2">
                    <?= htmlspecialchars($data['nama']); ?>
                </p>

                <div class="price mb-4">
                    Rp <?= $formattedHarga; ?>
                </div>

                <div class="d-flex gap-2">
                    <a href="detail.php?id=<?= $data['id']; ?>" class="btn btn-outline-success w-50">
                        Detail
                    </a>

                    <?php if (!$isTerjual): ?>
                        <a href="https://wa.me/62<?= $waClean; ?>?text=Saya%20tertarik%20dengan%20<?= urlencode($data['nama_hewan']); ?>"
                           target="_blank"
                           class="btn btn-success w-50">
                            <i class="bi bi-whatsapp"></i> Chat
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-50" disabled>
                            Sudah Terjual
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($totalPage > 1): ?>
    <div class="col-12">
        <nav aria-label="Navigasi Halaman Marketplace">
            <ul class="pagination justify-content-center mt-5">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link ajax-page-link" href="?page=<?= $page - 1; ?>#marketplace" data-page="<?= $page - 1; ?>">
                            &laquo; Sebelumnya
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPage; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link ajax-page-link" href="?page=<?= $i; ?>#marketplace" data-page="<?= $i; ?>">
                            <?= $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPage): ?>
                    <li class="page-item">
                        <a class="page-link ajax-page-link" href="?page=<?= $page + 1; ?>#marketplace" data-page="<?= $page + 1; ?>">
                            Selanjutnya &raquo;
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>
