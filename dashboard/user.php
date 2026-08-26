<?php

session_start();

include '../config/koneksi.php';

if(!isset($_SESSION['user'])){
    header("Location: ../home.php");
    exit;
}

if($_SESSION['user']['role'] != 'peternak'){
    header("Location: ../home.php");
    exit;
}

$user_id = (int)$_SESSION['user']['id'];


/* =====================================================
   TOTAL HEWAN
===================================================== */

$hewan_query = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM hewan
    WHERE user_id='$user_id'
");

$total_hewan = mysqli_fetch_assoc($hewan_query);


/* =====================================================
   TOTAL PENGELUARAN
===================================================== */

$pengeluaran_query = mysqli_query($conn, "
    SELECT COALESCE(SUM(p.nominal),0) AS total
    FROM pengeluaran p
    INNER JOIN hewan h
        ON p.hewan_id = h.id
    WHERE h.user_id='$user_id'
");

$total_pengeluaran = mysqli_fetch_assoc($pengeluaran_query);


/* =====================================================
   TOTAL HEWAN TERJUAL
===================================================== */

$terjual_query = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM hewan
    WHERE user_id='$user_id'
    AND status='Terjual'
    AND tanggal_terjual IS NOT NULL
");

$total_terjual = mysqli_fetch_assoc($terjual_query);


/* =====================================================
   PROFIT TEREALISASI
   HANYA DARI HEWAN YANG SUDAH TERJUAL
===================================================== */

$profit_query = mysqli_query($conn, "

    SELECT COALESCE(
        SUM(
            h.harga -
            COALESCE(
                (
                    SELECT SUM(p.nominal)
                    FROM pengeluaran p
                    WHERE p.hewan_id = h.id
                ),
                0
            )
        ),
        0
    ) AS total_profit

    FROM hewan h

    WHERE h.user_id='$user_id'
    AND h.status='Terjual'
    AND h.tanggal_terjual IS NOT NULL

");

$profit_data = mysqli_fetch_assoc($profit_query);

$total_profit = (float)($profit_data['total_profit'] ?? 0);


/* =====================================================
   AMBIL SEMUA HEWAN
===================================================== */

$hewan_result = mysqli_query($conn, "

    SELECT
        id,
        nama_hewan,
        jenis,
        harga,
        status,
        created_at,
        tanggal_terjual

    FROM hewan

    WHERE user_id='$user_id'

    ORDER BY created_at ASC

");


/* =====================================================
   ARRAY STATISTIK
===================================================== */

$statistik = [];

$periode_data = [];

$nama_bulan = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$nama_bulan_singkat = [
    1  => 'Jan',
    2  => 'Feb',
    3  => 'Mar',
    4  => 'Apr',
    5  => 'Mei',
    6  => 'Jun',
    7  => 'Jul',
    8  => 'Agu',
    9  => 'Sep',
    10 => 'Okt',
    11 => 'Nov',
    12 => 'Des'
];

/* =====================================================
   DATA STATISTIK PER TAHUN
===================================================== */

$tahun_data = [];

/* Tahun dari hewan ditambahkan */
$tahun_hewan_query = mysqli_query($conn, "

    SELECT DISTINCT YEAR(created_at) AS tahun
    FROM hewan
    WHERE user_id='$user_id'
    AND created_at IS NOT NULL

");

/* Tahun dari hewan terjual */
$tahun_terjual_query = mysqli_query($conn, "

    SELECT DISTINCT YEAR(tanggal_terjual) AS tahun
    FROM hewan
    WHERE user_id='$user_id'
    AND tanggal_terjual IS NOT NULL
    AND status='Terjual'

");

while($row = mysqli_fetch_assoc($tahun_hewan_query)){

    if(!empty($row['tahun'])){
        $tahun_data[] = (int)$row['tahun'];
    }

}

while($row = mysqli_fetch_assoc($tahun_terjual_query)){

    if(!empty($row['tahun'])){
        $tahun_data[] = (int)$row['tahun'];
    }

}

/* Hilangkan tahun duplikat */
$tahun_data = array_unique($tahun_data);

/* Urutkan tahun terbaru */
rsort($tahun_data);


/* Tahun default */
$tahun_default = !empty($tahun_data)
    ? $tahun_data[0]
    : (int)date('Y');


/* =====================================================
   DATA BULANAN
===================================================== */

$statistik_tahun = [];

foreach($tahun_data as $tahun){

    $statistik_tahun[$tahun] = [];

    for($bulan = 1; $bulan <= 12; $bulan++){

        $statistik_tahun[$tahun][$bulan] = [
            'hewan' => 0,
            'terjual' => 0
        ];

    }

}


/* =====================================================
   HITUNG HEWAN DITAMBAHKAN PER BULAN
===================================================== */

$query_bulanan_hewan = mysqli_query($conn, "

    SELECT
        YEAR(created_at) AS tahun,
        MONTH(created_at) AS bulan,
        COUNT(*) AS jumlah

    FROM hewan

    WHERE user_id='$user_id'
    AND created_at IS NOT NULL

    GROUP BY
        YEAR(created_at),
        MONTH(created_at)

");


while($row = mysqli_fetch_assoc($query_bulanan_hewan)){

    $tahun = (int)$row['tahun'];
    $bulan = (int)$row['bulan'];

    if(isset($statistik_tahun[$tahun][$bulan])){

        $statistik_tahun[$tahun][$bulan]['hewan'] =
            (int)$row['jumlah'];

    }

}


/* =====================================================
   HITUNG HEWAN TERJUAL PER BULAN
===================================================== */

$query_bulanan_terjual = mysqli_query($conn, "

    SELECT
        YEAR(tanggal_terjual) AS tahun,
        MONTH(tanggal_terjual) AS bulan,
        COUNT(*) AS jumlah

    FROM hewan

    WHERE user_id='$user_id'
    AND status='Terjual'
    AND tanggal_terjual IS NOT NULL

    GROUP BY
        YEAR(tanggal_terjual),
        MONTH(tanggal_terjual)

");


while($row = mysqli_fetch_assoc($query_bulanan_terjual)){

    $tahun = (int)$row['tahun'];
    $bulan = (int)$row['bulan'];

    if(isset($statistik_tahun[$tahun][$bulan])){

        $statistik_tahun[$tahun][$bulan]['terjual'] =
            (int)$row['jumlah'];

    }

}

/* =====================================================
   BENTUK DATA HEWAN
===================================================== */

$hewan_data = [];

while($h = mysqli_fetch_assoc($hewan_result)){

    $hewan_id = (int)$h['id'];

    $hewan_data[$hewan_id] = [
        'id' => $hewan_id,
        'nama' => $h['nama_hewan'],
        'jenis' => $h['jenis'],
        'harga' => (float)$h['harga'],
        'status' => $h['status'],
        'created_at' => $h['created_at'],
        'tanggal_terjual' => $h['tanggal_terjual'],
        'total_pengeluaran' => 0,
        'pengeluaran_detail' => []
    ];
}


/* =====================================================
   AMBIL PENGELUARAN
===================================================== */

$pengeluaran_result = mysqli_query($conn, "

    SELECT
        p.id,
        p.hewan_id,
        p.nominal,
        p.tanggal,
        p.keterangan,
        h.nama_hewan

    FROM pengeluaran p

    INNER JOIN hewan h
        ON p.hewan_id = h.id

    WHERE h.user_id='$user_id'

    ORDER BY p.tanggal ASC

");


while($p = mysqli_fetch_assoc($pengeluaran_result)){

    $hewan_id = (int)$p['hewan_id'];

    $nominal = (float)$p['nominal'];

    if(isset($hewan_data[$hewan_id])){

        $hewan_data[$hewan_id]['total_pengeluaran'] += $nominal;

        $hewan_data[$hewan_id]['pengeluaran_detail'][] = [
            'tanggal' => $p['tanggal'],
            'nominal' => $nominal,
            'keterangan' => $p['keterangan']
        ];
    }
}

/* =====================================================
   DATA PROFIT & PENGELUARAN PER BULAN
===================================================== */

$keuangan_tahun = [];

/* Buat struktur 12 bulan untuk setiap tahun */
foreach($tahun_data as $tahun){

    $keuangan_tahun[$tahun] = [];

    for($bulan = 1; $bulan <= 12; $bulan++){

        $keuangan_tahun[$tahun][$bulan] = [
            'pengeluaran' => 0,
            'profit' => 0
        ];

    }

}


/* =====================================================
   TOTAL PENGELUARAN PER BULAN
===================================================== */

$query_keuangan_pengeluaran = mysqli_query($conn, "

    SELECT
        YEAR(p.tanggal) AS tahun,
        MONTH(p.tanggal) AS bulan,
        COALESCE(SUM(p.nominal),0) AS total

    FROM pengeluaran p

    INNER JOIN hewan h
        ON p.hewan_id = h.id

    WHERE h.user_id='$user_id'
    AND p.tanggal IS NOT NULL

    GROUP BY
        YEAR(p.tanggal),
        MONTH(p.tanggal)

");


while($row = mysqli_fetch_assoc($query_keuangan_pengeluaran)){

    $tahun = (int)$row['tahun'];
    $bulan = (int)$row['bulan'];

    if(isset($keuangan_tahun[$tahun][$bulan])){

        $keuangan_tahun[$tahun][$bulan]['pengeluaran'] =
            (float)$row['total'];

    }

}


/* =====================================================
   PROFIT PER BULAN
   PROFIT = HARGA JUAL - TOTAL PENGELUARAN HEWAN
===================================================== */

foreach($hewan_data as $h){

    if(
        $h['status'] == 'Terjual' &&
        !empty($h['tanggal_terjual'])
    ){

        $tahun = (int)date(
            'Y',
            strtotime($h['tanggal_terjual'])
        );

        $bulan = (int)date(
            'm',
            strtotime($h['tanggal_terjual'])
        );

        if(isset($keuangan_tahun[$tahun][$bulan])){

            $profit =
                $h['harga'] -
                $h['total_pengeluaran'];

            $keuangan_tahun[$tahun][$bulan]['profit'] +=
                $profit;

        }

    }

}

/* =====================================================
   BENTUK AKTIVITAS BERDASARKAN TANGGAL
===================================================== */

foreach($hewan_data as $h){

    /* ==========================================
       1. HEWAN DITAMBAHKAN
    ========================================== */

    if(!empty($h['created_at'])){

        $tanggal = date('Y-m-d', strtotime($h['created_at']));

        if(!isset($statistik[$tanggal])){

            $statistik[$tanggal] = [
                'hewan' => [],
                'pengeluaran' => [],
                'terjual' => [],
                'jumlah_hewan' => 0,
                'jumlah_terjual' => 0,
                'total_pengeluaran' => 0,
                'total_penjualan' => 0,
                'total_profit' => 0
            ];
        }

        $statistik[$tanggal]['hewan'][] = [
            'nama' => $h['nama'],
            'jenis' => $h['jenis']
        ];

        $statistik[$tanggal]['jumlah_hewan']++;
    }


    /* ==========================================
       2. PENGELUARAN
       MENGIKUTI TANGGAL PENGELUARAN
    ========================================== */

    foreach($h['pengeluaran_detail'] as $p){

        $tanggal = date(
            'Y-m-d',
            strtotime($p['tanggal'])
        );

        if(!isset($statistik[$tanggal])){

            $statistik[$tanggal] = [
                'hewan' => [],
                'pengeluaran' => [],
                'terjual' => [],
                'jumlah_hewan' => 0,
                'jumlah_terjual' => 0,
                'total_pengeluaran' => 0,
                'total_penjualan' => 0,
                'total_profit' => 0
            ];
        }

        $statistik[$tanggal]['pengeluaran'][] = [
            'nama' => $h['nama'],
            'keterangan' => $p['keterangan'],
            'nominal' => $p['nominal']
        ];

        $statistik[$tanggal]['total_pengeluaran'] += $p['nominal'];
    }


    /* ==========================================
       3. HEWAN TERJUAL
       MENGIKUTI TANGGAL TERJUAL
    ========================================== */

    if(
        $h['status'] == 'Terjual' &&
        !empty($h['tanggal_terjual'])
    ){

        $tanggal = date(
            'Y-m-d',
            strtotime($h['tanggal_terjual'])
        );

        if(!isset($statistik[$tanggal])){

            $statistik[$tanggal] = [
                'hewan' => [],
                'pengeluaran' => [],
                'terjual' => [],
                'jumlah_hewan' => 0,
                'jumlah_terjual' => 0,
                'total_pengeluaran' => 0,
                'total_penjualan' => 0,
                'total_profit' => 0
            ];
        }

        $profit_hewan =
            $h['harga'] -
            $h['total_pengeluaran'];

        $statistik[$tanggal]['terjual'][] = [
            'nama' => $h['nama'],
            'harga' => $h['harga'],
            'total_pengeluaran' => $h['total_pengeluaran'],
            'profit' => $profit_hewan
        ];

        $statistik[$tanggal]['jumlah_terjual']++;

        $statistik[$tanggal]['total_penjualan'] +=
            $h['harga'];

        $statistik[$tanggal]['total_profit'] +=
            $profit_hewan;
    }
}


/* =====================================================
   URUTKAN TANGGAL
===================================================== */

ksort($statistik);


/* =====================================================
   BUAT DATA GRAFIK
===================================================== */

$label_statistik = [];

$data_tanggal = [];

$data_hewan = [];

$data_terjual = [];

$data_pengeluaran = [];

$data_profit = [];


/* =====================================================
   DETAIL UNTUK JAVASCRIPT
===================================================== */

$detail_statistik = [];


foreach($statistik as $tanggal => $data){

    $obj = new DateTime($tanggal);

    $hari = $obj->format('d');
    $bulan = (int)$obj->format('m');
    $tahun = (int)$obj->format('Y');

    $periode_key =
        $tahun . '-' .
        str_pad(
            $bulan,
            2,
            '0',
            STR_PAD_LEFT
        );



    /* ==========================================
       LABEL
    ========================================== */

    $label_statistik[] =
        $hari . ' ' .
        $nama_bulan_singkat[$bulan];


    $data_tanggal[] = $tanggal;

    $data_hewan[] =
        $data['jumlah_hewan'];

    $data_terjual[] =
        $data['jumlah_terjual'];

    $data_pengeluaran[] =
        $data['total_pengeluaran'];

    $data_profit[] =
        $data['total_profit'];


    /* ==========================================
       DETAIL
    ========================================== */

    $detail_statistik[$tanggal] = [
        'hewan' => $data['hewan'],
        'pengeluaran' => $data['pengeluaran'],
        'terjual' => $data['terjual']
    ];
}


?>

<!DOCTYPE html>

<html>

<head>

    <title>Dashboard Peternak</title>


    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">


    <style>

    /* =======================================
       DASHBOARD USER MODERN
    ======================================= */

    *{
        box-sizing:border-box;
    }


    body{

        margin:0;

        font-family:'Poppins',sans-serif;

        background:
        radial-gradient(
            circle at top left,
            rgba(46,125,50,.25),
            transparent 25%
        ),

        linear-gradient(
            135deg,
            #081018,
            #0d1b24,
            #09131d
        );

        min-height:100vh;

        color:white;
    }


    /* =======================================
       SIDEBAR
    ======================================= */

    .sidebar{

        width:270px;

        height:100vh;

        position:fixed;

        top:0;

        left:0;

        background:
        rgba(10,15,20,.88);

        backdrop-filter:blur(18px);

        border-right:
        1px solid
        rgba(255,255,255,.08);

        padding:30px 20px;

        z-index:999;
    }


    .sidebar h3{

        color:white;

        font-size:30px;

        font-weight:700;

        margin-bottom:25px;
    }


    .sidebar hr{

        border-color:
        rgba(255,255,255,.08);
    }


    .sidebar a{

        display:flex;

        align-items:center;

        gap:14px;

        text-decoration:none;

        color:#d7e5f2;

        padding:16px 18px;

        border-radius:18px;

        margin-bottom:14px;

        transition:.3s;
    }


    .sidebar a i{

        font-size:20px;
    }


    .sidebar a:hover{

        background:
        linear-gradient(
            135deg,
            #2E7D32,
            #00c853
        );

        transform:
        translateX(5px);

        color:white;

        box-shadow:
        0 10px 25px
        rgba(46,125,50,.35);
    }


    /* =======================================
       CONTENT
    ======================================= */

    .content{

        margin-left:270px;

        padding:40px;
    }


    .content h2{

        font-size:42px;

        font-weight:700;

        color:#fff;
    }


    .content > p{

        color:#b0bec5;
    }


    /* =======================================
       CARD
    ======================================= */

    .stats-card{

        background:
        rgba(255,255,255,.06)!important;

        border:
        1px solid
        rgba(255,255,255,.08);

        border-radius:30px;

        backdrop-filter:blur(20px);

        overflow:hidden;

        position:relative;

        color:white;

        transition:.4s;
    }


    .stats-card::before{

        content:'';

        position:absolute;

        width:180px;

        height:180px;

        background:
        rgba(255,255,255,.03);

        border-radius:50%;

        top:-70px;

        right:-70px;
    }


    .stats-card:hover{

        transform:
        translateY(-8px);

        box-shadow:
        0 20px 40px
        rgba(0,0,0,.35);
    }


    .stats-card h5{

        color:#b0bec5;

        margin-bottom:15px;
    }


    .stats-card h1{

        font-size:42px;

        font-weight:700;
    }


    /* =======================================
       CHART CARD
    ======================================= */

    .chart-card{

        background:
        rgba(255,255,255,.06)!important;

        border:
        1px solid
        rgba(255,255,255,.08);

        border-radius:30px;

        backdrop-filter:blur(20px);

        color:white;
    }


    .chart-card h4{

        font-weight:600;
    }


    /* =======================================
       PERIODE
    ======================================= */

    .periode-wrapper{

        display:flex;

        flex-direction:column;

        gap:5px;
    }


    .periode-wrapper label{

        color:#90a4ae;

        font-size:12px;

        font-weight:500;
    }


    #periodeChart{

        width:190px;

        background:#17232d;

        color:white;

        border:
        1px solid
        #33434f;

        border-radius:12px;

        padding:10px 14px;

        font-size:13px;
    }


    #periodeChart option{

        background:#17232d;

        color:white;
    }


    /* =======================================
       INDIKATOR
    ======================================= */

    .indicator-tabs{

        display:flex;

        flex-wrap:wrap;

        gap:10px;

        margin-top:10px;

        margin-bottom:20px;

        padding:6px;

        background:
        rgba(255,255,255,.035);

        border:
        1px solid
        rgba(255,255,255,.06);

        border-radius:16px;

        width:max-content;
    }


    .indicator-btn{

        border:none;

        background:transparent;

        color:#90a4ae;

        padding:10px 16px;

        border-radius:11px;

        font-size:13px;

        font-weight:500;

        cursor:pointer;

        transition:.3s;
    }


    .indicator-btn:hover{

        color:white;

        background:
        rgba(255,255,255,.06);
    }


    .indicator-btn.active{

        background:#2e7d32;

        color:white;

        box-shadow:
        0 5px 15px
        rgba(46,125,50,.25);
    }


    /* =======================================
       CHART
    ======================================= */

    .chart-wrapper{

        height:420px;

        position:relative;

        margin-top:10px;
    }

/* ==========================================
   PERBANDINGAN KEUANGAN
========================================== */

.keuangan-section{
    margin-top:50px;
}

.keuangan-title{
    color:white;
    font-size:22px;
    font-weight:700;
    margin-bottom:4px;
}

.keuangan-subtitle{
    color:#90a4ae;
    font-size:13px;
}

.keuangan-indicator-tabs{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:20px;
    margin-bottom:20px;
    padding:6px;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(255,255,255,.06);
    border-radius:16px;
    width:max-content;
}

.keuangan-indicator-btn{
    border:none;
    background:transparent;
    color:#90a4ae;
    padding:10px 16px;
    border-radius:11px;
    font-size:13px;
    font-weight:500;
    cursor:pointer;
    transition:.3s;
}

.keuangan-indicator-btn:hover{
    color:white;
    background:rgba(255,255,255,.06);
}

.keuangan-indicator-btn.active{
    background:#2e7d32;
    color:white;
    box-shadow:
        0 5px 15px
        rgba(46,125,50,.25);
}

.keuangan-chart-wrapper{
    height:420px;
    position:relative;
    margin-top:10px;
}


/* ==========================================
   JARAK REKAM JEJAK
========================================== */

.rekam-section{
    margin-top:65px;
}

.rekam-section-title{
    font-size:24px;
    font-weight:700;
    color:white;
}

.rekam-section-subtitle{
    color:#90a4ae;
    font-size:13px;
    margin-top:4px;
}

    
/* ==========================================
   REKAM JEJAK
========================================== */

.rekam-table{
    margin-bottom:0;
    color:white;
}

.rekam-table thead th{
    color:#90a4ae;
    background:rgba(255,255,255,.03);
    border-color:rgba(255,255,255,.08);
    font-size:12px;
    font-weight:600;
    padding:14px;
}

.rekam-table tbody td{
    color:#ffffff;
    background:rgba(255,255,255,.015);
    border-color:rgba(255,255,255,.06);
    padding:14px;
    font-size:13px;
}

.rekam-table tbody tr:hover{
    background:rgba(255,255,255,.05);
}

.rekam-tanggal{
    font-weight:600;
    color:white;
}

.rekam-periode{
    font-weight:600;
    color:white;
    font-size:14px;
}

.rekam-periode small{
    display:block;
    color:#90a4ae;
    font-size:11px;
    font-weight:400;
    margin-top:3px;
}

.rekam-jumlah{
    color:#90a4ae;
    font-size:12px;
    line-height:1.7;
}

.rekam-jumlah{
    color:#90a4ae;
    font-size:12px;
}

.btn-lihat-detail{
    border:1px solid rgba(255,255,255,.15);
    background:rgba(46,125,50,.15);
    color:#d7e5f2;
    border-radius:10px;
    padding:7px 13px;
    font-size:12px;
    cursor:pointer;
}

.btn-lihat-detail:hover{
    background:#2e7d32;
    color:white;
}

/* ==========================================
   MODAL DETAIL REKAM JEJAK
========================================== */

.detail-overlay{

    display:none;

    position:fixed;

    inset:0;

    background:rgba(0,0,0,.75);

    z-index:2000;

    align-items:center;

    justify-content:center;

    padding:25px;

}

.detail-overlay.show{

    display:flex;

}

.detail-modal-box{

    width:100%;

    max-width:1100px;

    max-height:90vh;

    overflow:hidden;

    background:#101a23;

    border:1px solid rgba(255,255,255,.10);

    border-radius:20px;

    box-shadow:0 25px 60px rgba(0,0,0,.5);

}

.detail-modal-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:20px 24px;

    border-bottom:1px solid rgba(255,255,255,.08);

}

.detail-modal-header h5{

    margin:0;

    color:white;

    font-weight:700;

}

.detail-modal-header small{

    color:#90a4ae;

}

.detail-close{

    width:40px;

    height:40px;

    border:none;

    border-radius:10px;

    background:rgba(255,255,255,.08);

    color:white;

    font-size:25px;

    cursor:pointer;

}

.detail-close:hover{

    background:#2e7d32;

}

.detail-modal-body{

    padding:20px 24px;

    max-height:60vh;

    overflow-y:auto;

}

.detail-table{

    width:100%;

    border-collapse:collapse;

}

.detail-table th{

    padding:13px;

    color:#90a4ae;

    text-align:left;

    border-bottom:1px solid rgba(255,255,255,.08);

}

.detail-table td{

    padding:13px;

    color:white;

    border-bottom:1px solid rgba(255,255,255,.06);

}

.detail-modal-footer{

    display:flex;

    justify-content:flex-end;

    padding:16px 24px;

    border-top:1px solid rgba(255,255,255,.08);

}

/* ==========================================
JARAK DIAGRAM KEUANGAN → REKAM JEJAK
========================================== */

.comparison-box{
    margin-top:50px;
}

/* ==========================================
   JUDUL REKAM JEJAK AKTIVITAS
========================================== */

.comparison-header strong{
    font-size:22px;
    font-weight:700;
    color:#ffffff;
    display:block;
}

.comparison-header .text-secondary{
    font-size:13px;
    color:#90a4ae!important;
}

    </style>

</head>


<body>


<!-- ==========================================
     SIDEBAR
========================================== -->

<div class="sidebar">

    <h3>🐄 TernakApp</h3>

    <hr>


    <a href="hewan/index.php">

        <i class="bi bi-grid-fill"></i>

        Data Hewan

    </a>


    <a href="hewan/tambah.php">

        <i class="bi bi-grid-fill"></i>

        Tambah Hewan

    </a>


    <a href="pengeluaran/index.php">

        <i class="bi bi-grid-fill"></i>

        Pengeluaran

    </a>


    <a href="keuntungan.php">

        <i class="bi bi-grid-fill"></i>

        Keuntungan

    </a>


    <a href="profil.php">

        Profil Saya

    </a>


    <a href="logout.php">

        <i class="bi bi-grid-fill"></i>

        Logout

    </a>


    <a href="../home.php"
       class="btn btn-dark">

        ← Kembali ke Home

    </a>

</div>



<!-- ==========================================
     CONTENT
========================================== -->

<div class="content">


    <h2>

        Selamat Datang,
        <?php echo htmlspecialchars($_SESSION['user']['nama']); ?>

    </h2>


    <p>

        Dashboard Peternak

    </p>



    <!-- ======================================
         STAT CARD
    ======================================= -->

 <div class="row g-4 mt-3">

    <!-- TOTAL HEWAN -->

    <div class="col-md-3">

        <div class="card-custom stats-card p-4">

            <h5>
                Total Hewan
            </h5>

            <h1>
                <?php
                echo number_format(
                    (int)$total_hewan['total'],
                    0,
                    ',',
                    '.'
                );
                ?>
            </h1>

            <small class="text-secondary">
                Semua hewan yang diposting
            </small>

        </div>

    </div>


    <!-- TOTAL PENGELUARAN -->

    <div class="col-md-3">

        <div class="card-custom stats-card p-4">

            <h5>
                Total Pengeluaran
            </h5>

            <h1>

                Rp
                <?php
                echo number_format(
                    (float)($total_pengeluaran['total'] ?: 0),
                    0,
                    ',',
                    '.'
                );
                ?>

            </h1>

            <small class="text-secondary">
                Semua biaya yang tercatat
            </small>

        </div>

    </div>


    <!-- TOTAL TERJUAL -->

    <div class="col-md-3">

        <div class="card-custom stats-card p-4">

            <h5>
                Total Terjual
            </h5>

            <h1>

                <?php
                echo number_format(
                    (int)$total_terjual['total'],
                    0,
                    ',',
                    '.'
                );
                ?>

            </h1>

            <small class="text-secondary">
                Hewan yang sudah terjual
            </small>

        </div>

    </div>


    <!-- PROFIT -->

    <div class="col-md-3">

        <div class="card-custom stats-card p-4">

            <h5>
                Profit Terealisasi
            </h5>

            <h1>

                Rp
                <?php
                echo number_format(
                    $total_profit,
                    0,
                    ',',
                    '.'
                );
                ?>

            </h1>

            <small class="text-secondary">
                Dari hewan yang sudah terjual
            </small>

        </div>

    </div>

</div>



    <!-- ======================================
         CHART
    ======================================= -->

    <div class="card-custom chart-card mt-4 p-4">


        <!-- HEADER -->

        <div class="d-flex justify-content-between align-items-center mb-3 chart-header">


            <div>

                <h4 class="mb-1">

                    📈 Perkembangan Aktivitas

                </h4>


                <p class="text-secondary mb-0">

                    Rekam jejak perkembangan usaha berdasarkan tanggal

                </p>

            </div>


<!-- DROPDOWN TAHUN -->

<div class="periode-wrapper">

    <label for="periodeChart">
        Tahun Statistik
    </label>

    <select id="periodeChart">

        <?php

        if(!empty($tahun_data)){

            foreach($tahun_data as $tahun){

                $selected =
                    ((int)$tahun === (int)$tahun_default)
                    ? 'selected'
                    : '';

                echo '<option value="' .
                    $tahun .
                    '" ' .
                    $selected .
                    '>' .
                    $tahun .
                    '</option>';

            }

        }else{

            echo '<option value="">Belum ada data</option>';

        }

        ?>

    </select>

</div>

        </div>



<div class="indicator-tabs">

    <button
        class="indicator-btn active"
        data-indicator="semua">

        Semua

    </button>

    <button
        class="indicator-btn"
        data-indicator="ditambahkan">

        🐄 Ditambahkan

    </button>

    <button
        class="indicator-btn"
        data-indicator="terjual">

        🛒 Terjual

    </button>

</div>



        <!-- ==================================
             CANVAS
        =================================== -->

        <div class="chart-wrapper">

            <canvas id="perkembanganChart"></canvas>

        </div>

<!-- ==================================
     DIAGRAM 2 - KEUANGAN
=================================== -->

<div class="card-custom chart-card mt-4 p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h4 class="mb-1">
                💰 Perbandingan Keuangan
            </h4>

            <p class="text-secondary mb-0">
                Perbandingan profit dan pengeluaran berdasarkan bulan
            </p>

        </div>

    </div>

    <div class="keuangan-indicator-tabs">

    <button
        class="keuangan-indicator-btn active"
        data-keuangan="semua">
        Semua
    </button>

    <button
        class="keuangan-indicator-btn"
        data-keuangan="pengeluaran">
        💸 Pengeluaran
    </button>

    <button
        class="keuangan-indicator-btn"
        data-keuangan="profit">
        💰 Profit
    </button>

</div>

    <div class="chart-wrapper">

        <canvas id="keuanganChart"></canvas>
        
    </div>

</div>

<!-- ==================================
     REKAM JEJAK
=================================== -->

<div class="comparison-box mt-5">

    <div class="comparison-header">

        <div>

            <strong>
                📋 Rekam Jejak Aktivitas
            </strong>

            <div class="text-secondary small mt-1">
                Riwayat aktivitas berdasarkan periode
            </div>

        </div>

    </div>


    <div class="table-responsive">

        <table class="table rekam-table align-middle">

            <thead>

                <tr>

                    <th>Periode</th>

                    <th>Aktivitas</th>

                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody id="rekamJejakBody">

            </tbody>

        </table>

    </div>


    <div
        id="rekamKosong"
        class="text-secondary text-center py-4"
        style="display:none;"
    >

        Belum ada aktivitas pada tahun ini.

    </div>

</div>



<!-- ==========================================
     MODAL DETAIL REKAM JEJAK
========================================== -->

<div id="detailAktivitasModal" class="detail-overlay">

    <div class="detail-modal-box">

        <div class="detail-modal-header">

            <div>

                <h5 id="detailModalTanggal">
                    Detail Aktivitas
                </h5>

                <small id="detailModalSubjudul">
                </small>

            </div>

        </div>


        <div class="detail-modal-body">

            <div class="table-responsive">

                <table class="detail-table">

                    <thead>

                        <tr>

                            <th>Aktivitas</th>
                            <th>Nama Hewan</th>
                            <th>Jenis</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>

                        </tr>

                    </thead>

                    <tbody id="detailModalBody">

                    </tbody>

                </table>

            </div>

        </div>


        <div class="detail-modal-footer">

            <button
                type="button"
                class="btn btn-secondary"
                onclick="tutupDetail()"
            >
                Tutup
            </button>

        </div>

    </div>

</div>

<!-- ==========================================
     CHART JS
========================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

/* =================================================
   DATA STATISTIK BULANAN
================================================= */

const statistikTahun =
    <?php echo json_encode(
        $statistik_tahun,
        JSON_UNESCAPED_UNICODE
    ); ?>;

const tahunDefault =
    <?php echo json_encode($tahun_default); ?>;

/* =================================================
   DATA KEUANGAN PER TAHUN
================================================= */

const keuanganTahun =
    <?php echo json_encode(
        $keuangan_tahun,
        JSON_UNESCAPED_UNICODE
    ); ?>;


/* =================================================
   NAMA BULAN
================================================= */

const labelsBulan = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];


/* =================================================
   CANVAS
================================================= */

const ctx =
    document
        .getElementById('perkembanganChart')
        .getContext('2d');


/* =================================================
   DATASET AWAL
================================================= */

const datasets = [

    {
        label:'Hewan Ditambahkan',

        data:[],

        backgroundColor:'#00c853',

        borderColor:'#00c853',

        borderWidth:1,

        borderRadius:7,

        borderSkipped:false
    },

    {
        label:'Hewan Terjual',

        data:[],

        backgroundColor:'#42a5f5',

        borderColor:'#42a5f5',

        borderWidth:1,

        borderRadius:7,

        borderSkipped:false
    }

];


/* =================================================
   CHART
================================================= */

const perkembanganChart =
    new Chart(ctx, {

        type:'bar',

        data:{

            labels:labelsBulan,

            datasets:datasets

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            interaction:{

                intersect:false,

                mode:'index'

            },

            plugins:{

                legend:{

                    labels:{

                        color:'#d7e5f2',

                        usePointStyle:true,

                        padding:18

                    }

                },

                tooltip:{

                    backgroundColor:
                        'rgba(8,16,24,.96)',

                    titleColor:'#fff',

                    bodyColor:'#d7e5f2',

                    padding:13,

                    cornerRadius:12,

                    callbacks:{

                        label:function(context){

                            return (
                                context.dataset.label +
                                ': ' +
                                Math.round(context.parsed.y) +
                                ' ekor'
                            );

                        }

                    }

                }

            },

            scales:{

                y:{

                    beginAtZero:true,

                    ticks:{

                        color:'#90a4ae',

                        precision:0,

                        callback:function(value){

                            return value + ' ekor';

                        }

                    },

                    title:{

                        display:true,

                        text:'Jumlah Hewan',

                        color:'#90a4ae'

                    },

                    grid:{

                        color:
                            'rgba(255,255,255,.05)'

                    }

                },

                x:{

                    ticks:{

                        color:'#90a4ae',

                        maxRotation:0,

                        minRotation:0

                    },

                    grid:{

                        display:false

                    }

                }

            }

        }

    });

/* =================================================
   DIAGRAM 2 - PROFIT VS PENGELUARAN
================================================= */

const ctxKeuangan =
    document
        .getElementById('keuanganChart')
        .getContext('2d');


const keuanganChart =
    new Chart(ctxKeuangan, {

        type:'bar',

        data:{

            labels:labelsBulan,

            datasets:[

                {
                    label:'Profit',

                    data:[],

                    backgroundColor:'#00c853',

                    borderColor:'#00c853',

                    borderWidth:1,

                    borderRadius:7,

                    borderSkipped:false
                },

                {
                    label:'Pengeluaran',

                    data:[],

                    backgroundColor:'#ffb300',

                    borderColor:'#ffb300',

                    borderWidth:1,

                    borderRadius:7,

                    borderSkipped:false
                }

            ]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            interaction:{

                intersect:false,

                mode:'index'

            },

            plugins:{

                legend:{

                    labels:{

                        color:'#d7e5f2',

                        usePointStyle:true,

                        padding:18

                    }

                },

                tooltip:{

                    backgroundColor:
                        'rgba(8,16,24,.96)',

                    titleColor:'#fff',

                    bodyColor:'#d7e5f2',

                    padding:13,

                    cornerRadius:12,

                    callbacks:{

                        label:function(context){

                            return (
                                context.dataset.label +
                                ': ' +
                                formatRupiah(
                                    context.parsed.y
                                )
                            );

                        }

                    }

                }

            },

            scales:{

                y:{

                    beginAtZero:true,

                    ticks:{

                        color:'#90a4ae',

                        callback:function(value){

                            return formatRupiah(value);

                        }

                    },

                    title:{

                        display:true,

                        text:'Nominal (Rupiah)',

                        color:'#90a4ae'

                    },

                    grid:{

                        color:
                            'rgba(255,255,255,.05)'

                    }

                },

                x:{

                    ticks:{

                        color:'#90a4ae',

                        maxRotation:0,

                        minRotation:0

                    },

                    grid:{

                        display:false

                    }

                }

            }

        }

    });


/* =================================================
   DATA REKAM JEJAK LAMA
   TETAP DIGUNAKAN UNTUK DETAIL
================================================= */

const detailStatistik =
    <?php echo json_encode(
        $detail_statistik,
        JSON_UNESCAPED_UNICODE
    ); ?>;


/* =================================================
   FORMAT RUPIAH
================================================= */

function formatRupiah(nilai){

    return 'Rp ' +
        new Intl.NumberFormat(
            'id-ID'
        ).format(
            Number(nilai) || 0
        );

}


/* =================================================
   ESCAPE HTML
================================================= */

function escapeHtml(text){

    const div =
        document.createElement('div');

    div.textContent =
        text ?? '';

    return div.innerHTML;

}


/* =================================================
   UPDATE DATA DIAGRAM
================================================= */

function updateChart(tahun){

    const data =
        statistikTahun[tahun];

    const dataDitambahkan = [];

    const dataTerjual = [];


    for(let bulan = 1; bulan <= 12; bulan++){

        if(data && data[bulan]){

            dataDitambahkan.push(
                Number(data[bulan].hewan) || 0
            );

            dataTerjual.push(
                Number(data[bulan].terjual) || 0
            );

        }else{

            dataDitambahkan.push(0);

            dataTerjual.push(0);

        }

    }


    perkembanganChart.data.labels =
        labelsBulan;

    perkembanganChart.data.datasets[0].data =
        dataDitambahkan;

    perkembanganChart.data.datasets[1].data =
        dataTerjual;


    perkembanganChart.update();

        /* ==========================================
       UPDATE DIAGRAM KEUANGAN
    ========================================== */

    const dataKeuangan =
        keuanganTahun[tahun];

    const dataProfit = [];

    const dataPengeluaran = [];


    for(
        let bulan = 1;
        bulan <= 12;
        bulan++
    ){

        if(
            dataKeuangan &&
            dataKeuangan[bulan]
        ){

            dataProfit.push(
                Number(
                    dataKeuangan[bulan].profit
                ) || 0
            );

            dataPengeluaran.push(
                Number(
                    dataKeuangan[bulan].pengeluaran
                ) || 0
            );

        }else{

            dataProfit.push(0);

            dataPengeluaran.push(0);

        }

    }


    keuanganChart.data.labels =
        labelsBulan;

    keuanganChart.data.datasets[0].data =
        dataProfit;

    keuanganChart.data.datasets[1].data =
        dataPengeluaran;


    keuanganChart.update();


    /* Rekam jejak mengikuti tahun */

    renderRekamJejak(tahun);

}


/* =================================================
   REKAM JEJAK BERDASARKAN PERIODE / BULAN
================================================= */

function renderRekamJejak(tahun){

    const tbody =
        document.getElementById(
            'rekamJejakBody'
        );

    const kosong =
        document.getElementById(
            'rekamKosong'
        );


    if(!tbody || !kosong){

        return;

    }


    tbody.innerHTML = '';

    let adaData = false;


    /*
     * Menyimpan aktivitas berdasarkan bulan
     *
     * Contoh:
     * 2026-08
     */

    const periode = {};


    Object.keys(detailStatistik)
        .sort()
        .forEach(function(tanggal){

            /*
             * Ambil tahun
             */

            if(
                tanggal.substring(0,4)
                !== String(tahun)
            ){

                return;

            }


            const data =
                detailStatistik[tanggal];


            const jumlahAktivitas =
                data.hewan.length +
                data.pengeluaran.length +
                data.terjual.length;


            if(jumlahAktivitas === 0){

                return;

            }


            /*
             * Ambil bulan
             */

            const tanggalObj =
                new Date(
                    tanggal + 'T00:00:00'
                );


            const bulan =
                tanggalObj.getMonth() + 1;


            const key =
                String(tahun) +
                '-' +
                String(bulan).padStart(2,'0');


            /*
             * Buat periode jika belum ada
             */

            if(!periode[key]){

                periode[key] = {

                    tahun: tahun,

                    bulan: bulan,

                    tanggal: [],

                    hewan: 0,

                    pengeluaran: 0,

                    terjual: 0

                };

            }


            /*
             * Simpan tanggal
             */

            periode[key].tanggal.push(
                tanggal
            );


            /*
             * Hitung aktivitas
             */

            periode[key].hewan +=
                data.hewan.length;


            periode[key].pengeluaran +=
                data.pengeluaran.length;


            periode[key].terjual +=
                data.terjual.length;

        });


    /*
     * Urutkan periode terbaru
     */

    const periodeKeys =
        Object.keys(periode)
            .sort()
            .reverse();


    /*
     * Tampilkan periode
     */

    periodeKeys.forEach(function(key){

        const item =
            periode[key];


        adaData = true;


        const namaBulan =
            labelsBulan[
                item.bulan - 1
            ];


        /*
         * Total aktivitas
         */

        const totalAktivitas =
            item.hewan +
            item.pengeluaran +
            item.terjual;


        /*
         * Ringkasan
         */

        let ringkasan = [];


        if(item.hewan > 0){

            ringkasan.push(
                item.hewan +
                ' hewan ditambahkan'
            );

        }


        if(item.pengeluaran > 0){

            ringkasan.push(
                item.pengeluaran +
                ' pengeluaran'
            );

        }


        if(item.terjual > 0){

            ringkasan.push(
                item.terjual +
                ' hewan terjual'
            );

        }


        /*
         * Tombol Lihat
         */

        const row =
            document.createElement('tr');


        row.innerHTML = `

            <td>

                <div class="rekam-periode">

                    ${namaBulan} ${tahun}

                    <small>
                        ${item.tanggal.length}
                        hari memiliki aktivitas
                    </small>

                </div>

            </td>


            <td>

                <div class="rekam-jumlah">

                    ${ringkasan.join(' • ')}

                    <br>

                    <span>
                        Total ${totalAktivitas} aktivitas
                    </span>

                </div>

            </td>


            <td>

                <button
                    type="button"
                    class="btn-lihat-detail"
                    onclick="lihatPeriode(
                        '${key}'
                    )"
                >

                    Lihat

                </button>

            </td>

        `;


        tbody.appendChild(row);

    });


    /*
     * Jika tidak ada data
     */

    if(adaData){

        kosong.style.display = 'none';

    }else{

        kosong.style.display = 'block';

    }

}

/* =================================================
   LIHAT DETAIL PERIODE
================================================= */

function lihatPeriode(key){

    /*
     * key contoh:
     * 2026-08
     */

    const bagian =
        key.split('-');


    const tahun =
        bagian[0];


    const bulan =
        Number(bagian[1]);


    const namaBulan =
        labelsBulan[bulan - 1];


    /*
     * Cari semua tanggal
     * pada periode tersebut
     */

    const tanggalPeriode =
        Object.keys(detailStatistik)
            .filter(function(tanggal){

                return (
                    tanggal.substring(0,7)
                    === key
                );

            })
            .sort()
            .reverse();


    if(tanggalPeriode.length === 0){

        alert(
            'Tidak ada aktivitas pada periode ini.'
        );

        return;

    }


    /*
     * Isi modal
     */

    document.getElementById(
        'detailModalTanggal'
    ).innerHTML =
        '📅 ' +
        namaBulan +
        ' ' +
        tahun;


    document.getElementById(
        'detailModalSubjudul'
    ).innerHTML =
        tanggalPeriode.length +
        ' hari memiliki aktivitas';


    let html = '';


    /*
     * Tampilkan tanggal
     */

    tanggalPeriode.forEach(
        function(tanggal){

            const data =
                detailStatistik[tanggal];


            const tanggalObj =
                new Date(
                    tanggal + 'T00:00:00'
                );


            const tanggalTampil =
                tanggalObj.toLocaleDateString(
                    'id-ID',
                    {
                        day:'numeric',
                        month:'long',
                        year:'numeric'
                    }
                );


            const jumlahAktivitas =
                data.hewan.length +
                data.pengeluaran.length +
                data.terjual.length;


            let aktivitas = [];


            if(data.hewan.length > 0){

                aktivitas.push(
                    '🐄 ' +
                    data.hewan.length +
                    ' hewan ditambahkan'
                );

            }


            if(data.pengeluaran.length > 0){

                aktivitas.push(
                    '💰 ' +
                    data.pengeluaran.length +
                    ' pengeluaran'
                );

            }


            if(data.terjual.length > 0){

                aktivitas.push(
                    '🛒 ' +
                    data.terjual.length +
                    ' hewan terjual'
                );

            }


            html += `

                <tr>

                    <td>

                        <strong>
                            ${tanggalTampil}
                        </strong>

                    </td>


                    <td>

                        ${aktivitas.join(
                            ' • '
                        )}

                    </td>


                    <td>

                        <button
                            type="button"
                            class="btn-lihat-detail"
                            onclick="tampilkanDetail(
                                '${tanggal}'
                            )"
                        >

                            Lihat Detail

                        </button>

                    </td>

                </tr>

            `;

        }
    );


    /*
     * Karena tabel modal lama punya
     * 5 kolom, kita ubah header sementara
     */

    const modalTable =
        document.querySelector(
            '#detailAktivitasModal .detail-table'
        );


    if(modalTable){

        modalTable.querySelector(
            'thead'
        ).innerHTML = `

            <tr>

                <th>Tanggal</th>

                <th>Aktivitas</th>

                <th>Aksi</th>

            </tr>

        `;

    }


    document.getElementById(
        'detailModalBody'
    ).innerHTML =
        html;


    /*
     * Sesuaikan colspan kosong
     */

    document.getElementById(
        'detailAktivitasModal'
    ).classList.add('show');

}


/* =================================================
   TAMPILKAN DETAIL REKAM JEJAK
================================================= */

function tampilkanDetail(tanggal){

    const data =
        detailStatistik[tanggal];


    if(!data){

        alert(
            'Detail aktivitas tidak ditemukan.'
        );

        return;

    }

    /*
 * Kembalikan header modal
 * menjadi header detail aktivitas
 */

const modalTable =
    document.querySelector(
        '#detailAktivitasModal .detail-table'
    );


if(modalTable){

    modalTable.querySelector(
        'thead'
    ).innerHTML = `

        <tr>

            <th>Aktivitas</th>
            <th>Nama Hewan</th>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th>Nominal</th>

        </tr>

    `;

}

    const tanggalObj =
        new Date(
            tanggal + 'T00:00:00'
        );


    const tanggalTampil =
        tanggalObj.toLocaleDateString(
            'id-ID',
            {
                day:'numeric',
                month:'long',
                year:'numeric'
            }
        );


    document.getElementById(
        'detailModalTanggal'
    ).innerHTML =
        '📋 ' + tanggalTampil;


    const jumlahAktivitas =
        data.hewan.length +
        data.pengeluaran.length +
        data.terjual.length;


    document.getElementById(
        'detailModalSubjudul'
    ).innerHTML =
        jumlahAktivitas +
        ' aktivitas pada tanggal ini';


    let html = '';


    /* ==========================================
       HEWAN DITAMBAHKAN
    ========================================== */

    data.hewan.forEach(
        function(item){

            html += `

                <tr>

                    <td>

                        <span class="badge bg-success">
                            Ditambahkan
                        </span>

                    </td>

                    <td>

                        <strong>
                            ${escapeHtml(item.nama)}
                        </strong>

                    </td>

                    <td>
                        ${escapeHtml(item.jenis)}
                    </td>

                    <td>
                        Hewan baru ditambahkan
                    </td>

                    <td class="text-end">
                        -
                    </td>

                </tr>

            `;

        }
    );


    /* ==========================================
       PENGELUARAN
    ========================================== */

    data.pengeluaran.forEach(
        function(item){

            html += `

                <tr>

                    <td>

                        <span class="badge bg-warning text-dark">
                            Pengeluaran
                        </span>

                    </td>

                    <td>

                        <strong>
                            ${escapeHtml(item.nama)}
                        </strong>

                    </td>

                    <td>
                        -
                    </td>

                    <td>

                        ${escapeHtml(
                            item.keterangan ||
                            'Pengeluaran'
                        )}

                    </td>

                    <td
                        class="text-end"
                        style="color:#ffb300;font-weight:600;"
                    >

                        ${formatRupiah(item.nominal)}

                    </td>

                </tr>

            `;

        }
    );


    /* ==========================================
       HEWAN TERJUAL
    ========================================== */

    data.terjual.forEach(
        function(item){

            html += `

                <tr>

                    <td>

                        <span class="badge bg-primary">
                            Terjual
                        </span>

                    </td>

                    <td>

                        <strong>
                            ${escapeHtml(item.nama)}
                        </strong>

                    </td>

                    <td>
                        -
                    </td>

                    <td>

                        Harga jual:
                        ${formatRupiah(item.harga)}

                        <br>

                        Pengeluaran:
                        ${formatRupiah(
                            item.total_pengeluaran
                        )}

                    </td>

                    <td class="text-end">

                        <div
                            style="
                                color:#42a5f5;
                                font-weight:600;
                            "
                        >

                            ${formatRupiah(item.harga)}

                        </div>

                        <div
                            style="
                                color:#00e676;
                                font-weight:700;
                            "
                        >

                            Profit:
                            ${formatRupiah(item.profit)}

                        </div>

                    </td>

                </tr>

            `;

        }
    );


    if(html === ''){

        html = `

            <tr>

                <td
                    colspan="5"
                    class="text-center"
                    style="color:#90a4ae;"
                >

                    Tidak ada aktivitas.

                </td>

            </tr>

        `;

    }


    document.getElementById(
        'detailModalBody'
    ).innerHTML =
        html;


    document.getElementById(
        'detailAktivitasModal'
    ).classList.add('show');

}


/* =================================================
   TUTUP DETAIL
================================================= */

function tutupDetail(){

    const modal =
        document.getElementById(
            'detailAktivitasModal'
        );

    modal.classList.remove('show');

}


/* =================================================
   INDIKATOR
================================================= */

const indicatorButtons =
    document.querySelectorAll(
        '.indicator-btn'
    );


indicatorButtons.forEach(
    function(button){

        button.addEventListener(
            'click',
            function(){

                indicatorButtons.forEach(
                    function(btn){

                        btn.classList.remove(
                            'active'
                        );

                    }
                );


                this.classList.add(
                    'active'
                );


                const indicator =
                    this.dataset.indicator;


                /* Sembunyikan semua */

                perkembanganChart.data.datasets
                    .forEach(
                        function(dataset){

                            dataset.hidden = true;

                        }
                    );


                /* Semua */

                if(
                    indicator === 'semua'
                ){

                    perkembanganChart.data.datasets
                        .forEach(
                            function(dataset){

                                dataset.hidden =
                                    false;

                            }
                        );

                }


                /* Ditambahkan */

                else if(
                    indicator === 'ditambahkan'
                ){

                    perkembanganChart
                        .data
                        .datasets[0]
                        .hidden = false;

                }


                /* Terjual */

                else if(
                    indicator === 'terjual'
                ){

                    perkembanganChart
                        .data
                        .datasets[1]
                        .hidden = false;

                }


                perkembanganChart.update();

            }
        );

    }
);


/* =================================================
   DROPDOWN TAHUN
================================================= */

document
    .getElementById('periodeChart')
    .addEventListener(
        'change',
        function(){

            updateChart(
                this.value
            );

        }
    );


/* =================================================
   LOAD AWAL
================================================= */

if(tahunDefault){

    updateChart(
        tahunDefault
    );

}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>