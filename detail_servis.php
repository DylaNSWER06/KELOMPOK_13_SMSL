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
$success_message = '';
$error_message = '';

// Ambil data servis
$query = "SELECT * FROM view_servis_lengkap WHERE id_servis = $id_servis";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: servis.php");
    exit;
}

$servis = $result->fetch_assoc();

// Proses tambah sparepart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_sparepart'])) {
    $id_sparepart = $_POST['id_sparepart'];
    $qty = $_POST['qty'];
    
    // Cek stok sparepart
    $check_stok = $conn->query("SELECT stok FROM sparepart WHERE id_sparepart = $id_sparepart");
    $stok_data = $check_stok->fetch_assoc();
    
    if ($stok_data['stok'] < $qty) {
        $error_message = "Stok sparepart tidak mencukupi!";
    } else {
        // Tambahkan detail sparepart (akan memicu trigger)
        if (tambahDetailSparepart($conn, $id_servis, $id_sparepart, $qty)) {
            $success_message = "Sparepart berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan sparepart: " . $conn->error;
        }
    }
}

// Proses tambah layanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_layanan'])) {
    $id_layanan = $_POST['id_layanan'];
    $qty = $_POST['qty'];
    
    if (tambahDetailLayanan($conn, $id_servis, $id_layanan, $qty)) {
        $success_message = "Layanan berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan layanan: " . $conn->error;
    }
}

// Proses hapus detail sparepart
if (isset($_GET['delete_sparepart']) && is_numeric($_GET['delete_sparepart'])) {
    $id_detail = $_GET['delete_sparepart'];
    
    // Hapus detail sparepart (akan memicu trigger)
    if (hapusDetailSparepart($conn, $id_detail)) {
        $success_message = "Sparepart berhasil dihapus!";
    } else {
        $error_message = "Gagal menghapus sparepart: " . $conn->error;
    }
}

// Proses hapus detail layanan
if (isset($_GET['delete_layanan']) && is_numeric($_GET['delete_layanan'])) {
    $id_detail = $_GET['delete_layanan'];
    
    if ($conn->query("DELETE FROM detail_layanan WHERE id_detail = $id_detail")) {
        $success_message = "Layanan berhasil dihapus!";
    } else {
        $error_message = "Gagal menghapus layanan: " . $conn->error;
    }
}

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

// Ambil data sparepart untuk dropdown
$query = "SELECT * FROM sparepart WHERE stok > 0 ORDER BY nama_part ASC";
$result = $conn->query($query);
$sparepart = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sparepart[] = $row;
    }
}

// Ambil data layanan untuk dropdown
$query = "SELECT * FROM layanan ORDER BY nama_layanan ASC";
$result = $conn->query($query);
$layanan = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $layanan[] = $row;
    }
}

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Detail Servis #<?= $id_servis ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="servis.php">Servis</a></li>
        <li class="breadcrumb-item active">Detail Servis #<?= $id_servis ?></li>
    </ol>
    
    <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $success_message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error_message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Informasi Servis
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">ID Servis</div>
                        <div class="col-md-8"><?= $servis['id_servis'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Pelanggan</div>
                        <div class="col-md-8"><?= $servis['nama_pelanggan'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Teknisi</div>
                        <div class="col-md-8"><?= $servis['nama_teknisi'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Masuk</div>
                        <div class="col-md-8"><?= $servis['tanggal_masuk'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Keluar</div>
                        <div class="col-md-8"><?= $servis['tanggal_keluar'] ? $servis['tanggal_keluar'] : '-' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status</div>
                        <div class="col-md-8">
                            <span class="badge <?= getStatusBadgeClass($servis['status']) ?>">
                                <?= $servis['status'] ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Keluhan</div>
                        <div class="col-md-8"><?= $servis['keluhan'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-money-bill me-1"></i>
                    Ringkasan Biaya
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 fw-bold">Total Biaya Sparepart</div>
                        <div class="col-md-6">Rp <?= number_format($total_sparepart, 0, ',', '.') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 fw-bold">Total Biaya Layanan</div>
                        <div class="col-md-6">Rp <?= number_format($total_layanan, 0, ',', '.') ?></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6 fw-bold">Total Biaya</div>
                        <div class="col-md-6 fw-bold fs-5">Rp <?= number_format($total_biaya, 0, ',', '.') ?></div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="cetak_invoice.php?id=<?= $id_servis ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-print me-1"></i> Cetak Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-microchip me-1"></i>
                    Sparepart yang Digunakan
                    <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahSparepartModal">
                        <i class="fas fa-plus"></i> Tambah Sparepart
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nama Part</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detail_sparepart)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada sparepart yang digunakan</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($detail_sparepart as $sp): ?>
                                <tr>
                                    <td><?= $sp['nama_part'] ?></td>
                                    <td><?= $sp['qty'] ?></td>
                                    <td>Rp <?= number_format($sp['harga'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($sp['total'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="detail_servis.php?id=<?= $id_servis ?>&delete_sparepart=<?= $sp['id_detail'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus sparepart ini?')">
                                            <i class="fas fa-trash"></i>
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
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list-alt me-1"></i>
                    Layanan yang Diberikan
                    <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahLayananModal">
                        <i class="fas fa-plus"></i> Tambah Layanan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nama Layanan</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detail_layanan)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada layanan yang diberikan</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($detail_layanan as $ly): ?>
                                <tr>
                                    <td><?= $ly['nama_layanan'] ?></td>
                                    <td><?= $ly['qty'] ?></td>
                                    <td>Rp <?= number_format($ly['harga'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($ly['total'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="detail_servis.php?id=<?= $id_servis ?>&delete_layanan=<?= $ly['id_detail'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                                            <i class="fas fa-trash"></i>
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
    </div>
</div>

<!-- Modal Tambah Sparepart -->
<div class="modal fade" id="tambahSparepartModal" tabindex="-1" aria-labelledby="tambahSparepartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahSparepartModalLabel">Tambah Sparepart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_sparepart" class="form-label">Sparepart</label>
                        <select class="form-select" id="id_sparepart" name="id_sparepart" required>
                            <option value="" selected disabled>Pilih Sparepart</option>
                            <?php foreach ($sparepart as $sp): ?>
                            <option value="<?= $sp['id_sparepart'] ?>"><?= $sp['nama_part'] ?> - Stok: <?= $sp['stok'] ?> - Rp <?= number_format($sp['harga'], 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="qty" class="form-label">Jumlah</label>
                        <input type="number" class="form-control" id="qty" name="qty" min="1" value="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_sparepart" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Layanan -->
<div class="modal fade" id="tambahLayananModal" tabindex="-1" aria-labelledby="tambahLayananModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahLayananModalLabel">Tambah Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_layanan" class="form-label">Layanan</label>
                        <select class="form-select" id="id_layanan" name="id_layanan" required>
                            <option value="" selected disabled>Pilih Layanan</option>
                            <?php foreach ($layanan as $ly): ?>
                            <option value="<?= $ly['id_layanan'] ?>"><?= $ly['nama_layanan'] ?> - Rp <?= number_format($ly['harga'], 0, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="qty" class="form-label">Jumlah</label>
                        <input type="number" class="form-control" id="qty" name="qty" min="1" value="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_layanan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
