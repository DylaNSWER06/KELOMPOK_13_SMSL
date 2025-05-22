<?php
session_start();
include 'config/database.php';
include 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data untuk dashboard
$totalPelanggan = getTotalCount($conn, 'pelanggan');
$totalTeknisi = getTotalCount($conn, 'teknisi');
$totalServis = getTotalCount($conn, 'servis');
$totalSparepart = getTotalCount($conn, 'sparepart');

// Ambil data servis terbaru
$query = "SELECT * FROM view_servis_lengkap ORDER BY tanggal_masuk DESC LIMIT 5";
$result = $conn->query($query);
$latestServices = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $latestServices[] = $row;
    }
}

// Ambil data sparepart dengan stok rendah
$query = "SELECT * FROM view_stok_sparepart WHERE stok < 5 ORDER BY stok ASC";
$result = $conn->query($query);
$lowStockParts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lowStockParts[] = $row;
    }
}

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Pelanggan</div>
                        <div><h2><?= $totalPelanggan ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="pelanggan.php">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Teknisi</div>
                        <div><h2><?= $totalTeknisi ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="teknisi.php">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Servis</div>
                        <div><h2><?= $totalServis ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="servis.php">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Sparepart</div>
                        <div><h2><?= $totalSparepart ?></h2></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="sparepart.php">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    Servis Terbaru
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Teknisi</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestServices as $service): ?>
                                <tr>
                                    <td><?= $service['id_servis'] ?></td>
                                    <td><?= $service['nama_pelanggan'] ?></td>
                                    <td><?= $service['nama_teknisi'] ?></td>
                                    <td><?= $service['tanggal_masuk'] ?></td>
                                    <td>
                                        <span class="badge <?= getStatusBadgeClass($service['status']) ?>">
                                            <?= $service['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Sparepart Stok Rendah
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Part</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockParts as $part): ?>
                                <tr>
                                    <td><?= $part['id_sparepart'] ?></td>
                                    <td><?= $part['nama_part'] ?></td>
                                    <td>Rp <?= number_format($part['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge <?= ($part['stok'] <= 2) ? 'bg-danger' : 'bg-warning' ?>">
                                            <?= $part['stok'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
