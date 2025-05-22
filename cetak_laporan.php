<?php
session_start();
include 'config/database.php';
include 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Set default tanggal
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-t');

// Ambil data servis berdasarkan tanggal
$query = "SELECT * FROM view_servis_lengkap 
          WHERE tanggal_masuk BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
          ORDER BY tanggal_masuk DESC";
$result = $conn->query($query);
$servis = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $servis[] = $row;
    }
}

// Ambil data penggunaan sparepart
$query = "SELECT * FROM view_penggunaan_sparepart 
          WHERE id_servis IN (
              SELECT id_servis FROM servis 
              WHERE tanggal_masuk BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
          )";
$result = $conn->query($query);
$penggunaan_sparepart = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $penggunaan_sparepart[] = $row;
    }
}

// Hitung total pendapatan
$total_pendapatan = 0;
$query = "SELECT 
            SUM(dl.qty * l.harga) + IFNULL(SUM(ds.qty * s.harga), 0) as total
          FROM 
            servis sv
            LEFT JOIN detail_layanan dl ON sv.id_servis = dl.id_servis
            LEFT JOIN layanan l ON dl.id_layanan = l.id_layanan
            LEFT JOIN detail_sparepart ds ON sv.id_servis = ds.id_servis
            LEFT JOIN sparepart s ON ds.id_sparepart = s.id_sparepart
          WHERE 
            sv.tanggal_masuk BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
            AND sv.status = 'Selesai'";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_pendapatan = $row['total'] ? $row['total'] : 0;
}

// Hitung jumlah servis berdasarkan status
$status_counts = [
    'Menunggu' => 0,
    'Proses' => 0,
    'Selesai' => 0,
    'Batal' => 0
];

$query = "SELECT status, COUNT(*) as count FROM servis 
          WHERE tanggal_masuk BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
          GROUP BY status";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Laporan Servis - <?= $tanggal_awal ?> s/d <?= $tanggal_akhir ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .report-title {
            margin-bottom: 20px;
            text-align: center;
        }
        .report-title h2 {
            margin-bottom: 0;
        }
        .report-title h3 {
            margin-top: 0;
            font-size: 18px;
        }
        .table {
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="report-title">
                    <h2>LAPORAN SERVIS LAPTOP</h2>
                    <h3>Periode: <?= date('d-m-Y', strtotime($tanggal_awal)) ?> s/d <?= date('d-m-Y', strtotime($tanggal_akhir)) ?></h3>
                </div>
                <hr>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pendapatan</h5>
                                <h3 class="card-text">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Menunggu/Proses</h5>
                                <h3 class="card-text"><?= $status_counts['Menunggu'] + $status_counts['Proses'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Selesai</h5>
                                <h3 class="card-text"><?= $status_counts['Selesai'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Batal</h5>
                                <h3 class="card-text"><?= $status_counts['Batal'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Servis</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Teknisi</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Tanggal Keluar</th>
                                        <th>Status</th>
                                        <th>Keluhan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($servis)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data servis pada periode ini</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($servis as $s): ?>
                                    <tr>
                                        <td><?= $s['id_servis'] ?></td>
                                        <td><?= $s['nama_pelanggan'] ?></td>
                                        <td><?= $s['nama_teknisi'] ?></td>
                                        <td><?= $s['tanggal_masuk'] ?></td>
                                        <td><?= $s['tanggal_keluar'] ? $s['tanggal_keluar'] : '-' ?></td>
                                        <td><?= $s['status'] ?></td>
                                        <td><?= $s['keluhan'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Penggunaan Sparepart</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Servis</th>
                                        <th>Nama Part</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($penggunaan_sparepart)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada penggunaan sparepart pada periode ini</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($penggunaan_sparepart as $ps): ?>
                                    <tr>
                                        <td><?= $ps['id_servis'] ?></td>
                                        <td><?= $ps['nama_part'] ?></td>
                                        <td><?= $ps['qty'] ?></td>
                                        <td>Rp <?= number_format($ps['harga'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($ps['total_harga'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Laporan ini dicetak pada tanggal <?= date('d-m-Y H:i:s') ?></p>
            <p>Sistem Manajemen Servis Laptop &copy; <?= date('Y') ?></p>
        </div>
        
        <div class="row mt-4 no-print">
            <div class="col-12 text-center">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
                <a href="laporan.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            // Auto print when page loads
            // window.print();
        }
    </script>
</body>
</html>
