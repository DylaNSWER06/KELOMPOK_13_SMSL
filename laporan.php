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

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Laporan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Laporan</li>
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
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Total Pendapatan</div>
                        <div><h4>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h4></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Servis Menunggu/Proses</div>
                        <div><h4><?= $status_counts['Menunggu'] + $status_counts['Proses'] ?></h4></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Servis Selesai</div>
                        <div><h4><?= $status_counts['Selesai'] ?></h4></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Servis Batal</div>
                        <div><h4><?= $status_counts['Batal'] ?></h4></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Laporan Servis
            <div class="float-end">
                <a href="cetak_laporan.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
                <a href="analisis_sparepart.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-pie"></i> Analisis Sparepart
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Teknisi</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Keluar</th>
                            <th>Status</th>
                            <th>Aksi</th>
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
                            <td>
                                <span class="badge <?= getStatusBadgeClass($s['status']) ?>">
                                    <?= $s['status'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_servis.php?id=<?= $s['id_servis'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-microchip me-1"></i>
            Penggunaan Sparepart
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
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

<?php include 'templates/footer.php'; ?>
