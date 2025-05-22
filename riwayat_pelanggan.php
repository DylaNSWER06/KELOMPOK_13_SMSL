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
    header("Location: pelanggan.php");
    exit;
}

$id_pelanggan = $_GET['id'];

// Ambil data pelanggan
$query = "SELECT * FROM pelanggan WHERE id_pelanggan = $id_pelanggan";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: pelanggan.php");
    exit;
}

$pelanggan = $result->fetch_assoc();

// Ambil riwayat servis pelanggan menggunakan stored procedure
$riwayat = getRiwayatServisPelanggan($conn, $id_pelanggan);

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Riwayat Servis Pelanggan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="pelanggan.php">Pelanggan</a></li>
        <li class="breadcrumb-item active">Riwayat Servis</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user me-1"></i>
            Data Pelanggan
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2 fw-bold">Nama</div>
                <div class="col-md-10"><?= $pelanggan['nama_pelanggan'] ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2 fw-bold">No. HP</div>
                <div class="col-md-10"><?= $pelanggan['no_hp'] ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2 fw-bold">Email</div>
                <div class="col-md-10"><?= $pelanggan['email'] ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2 fw-bold">Alamat</div>
                <div class="col-md-10"><?= $pelanggan['alamat'] ?></div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Riwayat Servis
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID Servis</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Keluar</th>
                            <th>Status</th>
                            <th>Teknisi</th>
                            <th>Keluhan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayat)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada riwayat servis</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= $r['id_servis'] ?></td>
                            <td><?= $r['tanggal_masuk'] ?></td>
                            <td><?= $r['tanggal_keluar'] ? $r['tanggal_keluar'] : '-' ?></td>
                            <td>
                                <span class="badge <?= getStatusBadgeClass($r['status']) ?>">
                                    <?= $r['status'] ?>
                                </span>
                            </td>
                            <td><?= $r['nama_teknisi'] ?></td>
                            <td><?= $r['keluhan'] ?></td>
                            <td>
                                <a href="detail_servis.php?id=<?= $r['id_servis'] ?>" class="btn btn-sm btn-primary">
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
</div>

<?php include 'templates/footer.php'; ?>
