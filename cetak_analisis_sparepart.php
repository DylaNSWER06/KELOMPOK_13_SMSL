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

// Ambil data analisis penggunaan sparepart menggunakan stored procedure dengan looping
$analisis_sparepart = getAnalisisPenggunaanSparepart($conn, $tanggal_awal, $tanggal_akhir);

// Hitung total nilai
$total_nilai = 0;
foreach ($analisis_sparepart as $item) {
    $total_nilai += $item['nilai_total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Analisis Penggunaan Sparepart - <?= $tanggal_awal ?> s/d <?= $tanggal_akhir ?></title>
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
        .progress {
            height: 20px;
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
                    <h2>ANALISIS PENGGUNAAN SPAREPART</h2>
                    <h3>Periode: <?= date('d-m-Y', strtotime($tanggal_awal)) ?> s/d <?= date('d-m-Y', strtotime($tanggal_akhir)) ?></h3>
                </div>
                <hr>
                
                <div class="row mb-4">
                    <div class="col-md-6 offset-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Nilai Penggunaan Sparepart</h5>
                                <h3 class="card-text">Rp <?= number_format($total_nilai, 0, ',', '.') ?></h3>
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
                        <h5 class="mb-0">Analisis Penggunaan Sparepart</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Sparepart</th>
                                        <th>Jumlah Penggunaan</th>
                                        <th>Nilai Total</th>
                                        <th>Persentase Nilai</th>
                                        <th>Grafik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($analisis_sparepart)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data penggunaan sparepart pada periode ini</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($analisis_sparepart as $item): ?>
                                    <tr>
                                        <td><?= $item['id_sparepart'] ?></td>
                                        <td><?= $item['nama_sparepart'] ?></td>
                                        <td><?= $item['jumlah_penggunaan'] ?></td>
                                        <td>Rp <?= number_format($item['nilai_total'], 0, ',', '.') ?></td>
                                        <td><?= number_format($item['persentase_nilai'], 2) ?>%</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $item['persentase_nilai'] ?>%;" 
                                                     aria-valuenow="<?= $item['persentase_nilai'] ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?= number_format($item['persentase_nilai'], 2) ?>%
                                                </div>
                                            </div>
                                        </td>
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
                    <i class="fas fa-print"></i> Cetak Analisis
                </button>
                <a href="analisis_sparepart.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>" class="btn btn-secondary">
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
