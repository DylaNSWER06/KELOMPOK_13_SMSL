<?php
session_start();
include 'config/database.php';
include 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ada parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: servis.php");
    exit;
}

$id_servis = $_GET['id'];

// Ambil data servis
$query = "SELECT * FROM view_servis_lengkap WHERE id_servis = $id_servis";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: servis.php");
    exit;
}

$servis = $result->fetch_assoc();

// Ambil detail sparepart
$query = "SELECT ds.id_detail, s.nama_part, ds.qty, s.harga, (ds.qty * s.harga) as total 
          FROM detail_sparepart ds 
          JOIN sparepart s ON ds.id_sparepart = s.id_sparepart 
          WHERE ds.id_servis = $id_servis";
$result = $conn->query($query);
$detail_sparepart = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $detail_sparepart[] = $row;
    }
}

// Ambil detail layanan
$query = "SELECT dl.id_detail, l.nama_layanan, dl.qty, l.harga, (dl.qty * l.harga) as total 
          FROM detail_layanan dl 
          JOIN layanan l ON dl.id_layanan = l.id_layanan 
          WHERE dl.id_servis = $id_servis";
$result = $conn->query($query);
$detail_layanan = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $detail_layanan[] = $row;
    }
}

// Hitung total biaya
$total_sparepart = 0;
foreach ($detail_sparepart as $sp) {
    $total_sparepart += $sp['total'];
}

$total_layanan = 0;
foreach ($detail_layanan as $ly) {
    $total_layanan += $ly['total'];
}

$total_biaya = $total_sparepart + $total_layanan;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Invoice Servis #<?= $id_servis ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .invoice-title {
            margin-bottom: 20px;
            text-align: center;
        }
        .invoice-title h2 {
            margin-bottom: 0;
        }
        .invoice-title h3 {
            margin-top: 0;
            font-size: 18px;
        }
        .table {
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .total-row {
            font-weight: bold;
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
                <div class="invoice-title">
                    <h2>INVOICE SERVIS</h2>
                    <h3>Sistem Manajemen Servis Laptop</h3>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <address>
                            <strong>Data Pelanggan:</strong><br>
                            <?= $servis['nama_pelanggan'] ?><br>
                            <?php
                            // Ambil data pelanggan
                            $query = "SELECT * FROM pelanggan WHERE id_pelanggan = " . $servis['id_pelanggan'];
                            $result = $conn->query($query);
                            $pelanggan = $result->fetch_assoc();
                            ?>
                            <?= $pelanggan['alamat'] ?><br>
                            <?= $pelanggan['no_hp'] ?><br>
                            <?= $pelanggan['email'] ?>
                        </address>
                    </div>
                    <div class="col-6 text-end">
                        <address>
                            <strong>Info Servis:</strong><br>
                            ID Servis: #<?= $servis['id_servis'] ?><br>
                            Tanggal Masuk: <?= $servis['tanggal_masuk'] ?><br>
                            Tanggal Keluar: <?= $servis['tanggal_keluar'] ? $servis['tanggal_keluar'] : '-' ?><br>
                            Status: <?= $servis['status'] ?><br>
                            Teknisi: <?= $servis['nama_teknisi'] ?>
                        </address>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <address>
                            <strong>Keluhan:</strong><br>
                            <?= $servis['keluhan'] ?>
                        </address>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Rincian Sparepart</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Part</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($detail_sparepart)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada sparepart yang digunakan</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($detail_sparepart as $sp): ?>
                                    <tr>
                                        <td><?= $sp['nama_part'] ?></td>
                                        <td class="text-center"><?= $sp['qty'] ?></td>
                                        <td class="text-end">Rp <?= number_format($sp['harga'], 0, ',', '.') ?></td>
                                        <td class="text-end">Rp <?= number_format($sp['total'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="3" class="text-end">Subtotal Sparepart:</td>
                                        <td class="text-end">Rp <?= number_format($total_sparepart, 0, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Rincian Layanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Layanan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($detail_layanan)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada layanan yang diberikan</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($detail_layanan as $ly): ?>
                                    <tr>
                                        <td><?= $ly['nama_layanan'] ?></td>
                                        <td class="text-center"><?= $ly['qty'] ?></td>
                                        <td class="text-end">Rp <?= number_format($ly['harga'], 0, ',', '.') ?></td>
                                        <td class="text-end">Rp <?= number_format($ly['total'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="3" class="text-end">Subtotal Layanan:</td>
                                        <td class="text-end">Rp <?= number_format($total_layanan, 0, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr class="total-row">
                                        <td class="text-end" width="80%">Total Biaya:</td>
                                        <td class="text-end">Rp <?= number_format($total_biaya, 0, ',', '.') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-6">
                <p><strong>Catatan:</strong></p>
                <p>
                    1. Garansi servis berlaku selama 7 hari sejak tanggal keluar.<br>
                    2. Garansi tidak berlaku jika segel rusak.<br>
                    3. Barang yang tidak diambil dalam 30 hari bukan tanggung jawab kami.
                </p>
            </div>
            <div class="col-6 text-end">
                <p>
                    <strong>Hormat Kami,</strong><br><br><br><br>
                    _________________<br>
                    Teknisi
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>Terima kasih telah mempercayakan laptop Anda kepada kami.</p>
            <p>Sistem Manajemen Servis Laptop &copy; <?= date('Y') ?></p>
        </div>
        
        <div class="row mt-4 no-print">
            <div class="col-12 text-center">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Invoice
                </button>
                <a href="detail_servis.php?id=<?= $id_servis ?>" class="btn btn-secondary">
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
