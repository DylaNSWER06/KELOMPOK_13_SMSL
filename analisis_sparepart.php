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

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Analisis Penggunaan Sparepart</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="laporan.php">Laporan</a></li>
        <li class="breadcrumb-item active">Analisis Sparepart</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar me-1"></i>
            Filter Tanggal
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="<?= $tanggal_awal ?>">
                </div>
                <div class="col-md-4">
                    <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?= $tanggal_akhir ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-pie me-1"></i>
            Analisis Penggunaan Sparepart
            <a href="cetak_analisis_sparepart.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>" class="btn btn-primary btn-sm float-end" target="_blank">
                <i class="fas fa-print"></i> Cetak Analisis
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
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
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Grafik Penggunaan Sparepart
                </div>
                <div class="card-body">
                    <canvas id="chartPenggunaan" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Distribusi Nilai Sparepart
                </div>
                <div class="card-body">
                    <canvas id="chartNilai" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk grafik
    const labels = <?= json_encode(array_column($analisis_sparepart, 'nama_sparepart')) ?>;
    const jumlahPenggunaan = <?= json_encode(array_column($analisis_sparepart, 'jumlah_penggunaan')) ?>;
    const nilaiTotal = <?= json_encode(array_column($analisis_sparepart, 'nilai_total')) ?>;
    const persentaseNilai = <?= json_encode(array_column($analisis_sparepart, 'persentase_nilai')) ?>;
    
    // Grafik Penggunaan Sparepart
    const ctxPenggunaan = document.getElementById('chartPenggunaan').getContext('2d');
    new Chart(ctxPenggunaan, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Penggunaan',
                data: jumlahPenggunaan,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Grafik Distribusi Nilai Sparepart
    const ctxNilai = document.getElementById('chartNilai').getContext('2d');
    new Chart(ctxNilai, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Persentase Nilai',
                data: persentaseNilai,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(199, 199, 199, 0.2)',
                    'rgba(83, 102, 255, 0.2)',
                    'rgba(40, 159, 64, 0.2)',
                    'rgba(210, 199, 199, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)',
                    'rgba(40, 159, 64, 1)',
                    'rgba(210, 199, 199, 1)'
                ],
                borderWidth: 1
            }]
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>
