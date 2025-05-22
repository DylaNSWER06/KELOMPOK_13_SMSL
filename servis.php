<?php
session_start();
include 'config/database.php';
include 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success_message = '';
$error_message = '';

// Proses form tambah servis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_servis'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $id_teknisi = $_POST['id_teknisi'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $status = $_POST['status'];
    $keluhan = $_POST['keluhan'];
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Tambah servis menggunakan stored procedure
        if (tambahServis($conn, $id_pelanggan, $id_teknisi, $tanggal_masuk, $status, $keluhan)) {
            // Dapatkan ID servis yang baru saja ditambahkan
            $id_servis = $conn->insert_id;
            
            // Commit transaksi
            $conn->commit();
            
            $success_message = "Servis berhasil ditambahkan! ID Servis: " . $id_servis;
            
            // Redirect ke halaman detail servis
            header("Location: detail_servis.php?id=" . $id_servis);
            exit;
        } else {
            throw new Exception("Gagal menambahkan servis");
        }
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();
        $error_message = "Gagal menambahkan servis: " . $e->getMessage();
    }
}

// Proses update status servis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id_servis = $_POST['id_servis'];
    $status_baru = $_POST['status_baru'];
    $tanggal_keluar = ($status_baru == 'Selesai') ? date('Y-m-d') : null;
    
    if (updateStatusServis($conn, $id_servis, $status_baru, $tanggal_keluar)) {
        $success_message = "Status servis berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui status servis: " . $conn->error;
    }
}

// Ambil data servis dari view
$servis = getDataFromView($conn, 'view_servis_lengkap');

// Ambil data pelanggan untuk dropdown
$query = "SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC";
$result = $conn->query($query);
$pelanggan = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pelanggan[] = $row;
    }
}

// Ambil data teknisi untuk dropdown
$query = "SELECT * FROM teknisi ORDER BY nama_teknisi ASC";
$result = $conn->query($query);
$teknisi = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teknisi[] = $row;
    }
}

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Data Servis</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Servis</li>
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
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Data Servis
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahServisModal">
                <i class="fas fa-plus"></i> Tambah Servis
            </button>
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
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?= $s['id_servis'] ?>">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal Update Status -->
                        <div class="modal fade" id="updateStatusModal<?= $s['id_servis'] ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel<?= $s['id_servis'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateStatusModalLabel<?= $s['id_servis'] ?>">Update Status Servis #<?= $s['id_servis'] ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_servis" value="<?= $s['id_servis'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="status_baru" class="form-label">Status Baru</label>
                                                <select class="form-select" id="status_baru" name="status_baru" required>
                                                    <option value="" selected disabled>Pilih Status</option>
                                                    <option value="Menunggu" <?= ($s['status'] == 'Menunggu') ? 'selected' : '' ?>>Menunggu</option>
                                                    <option value="Proses" <?= ($s['status'] == 'Proses') ? 'selected' : '' ?>>Proses</option>
                                                    <option value="Selesai" <?= ($s['status'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                                    <option value="Batal" <?= ($s['status'] == 'Batal') ? 'selected' : '' ?>>Batal</option>
                                                </select>
                                                <small class="form-text text-muted">Jika status diubah menjadi "Selesai", tanggal keluar akan otomatis diisi dengan tanggal hari ini.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_status" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Servis -->
<div class="modal fade" id="tambahServisModal" tabindex="-1" aria-labelledby="tambahServisModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahServisModalLabel">Tambah Servis Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_pelanggan" class="form-label">Pelanggan</label>
                        <select class="form-select" id="id_pelanggan" name="id_pelanggan" required>
                            <option value="" selected disabled>Pilih Pelanggan</option>
                            <?php foreach ($pelanggan as $p): ?>
                            <option value="<?= $p['id_pelanggan'] ?>"><?= $p['nama_pelanggan'] ?> - <?= $p['no_hp'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_teknisi" class="form-label">Teknisi</label>
                        <select class="form-select" id="id_teknisi" name="id_teknisi" required>
                            <option value="" selected disabled>Pilih Teknisi</option>
                            <?php foreach ($teknisi as $t): ?>
                            <option value="<?= $t['id_teknisi'] ?>"><?= $t['nama_teknisi'] ?> - <?= $t['spesialis'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                        <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Menunggu" selected>Menunggu</option>
                            <option value="Proses">Proses</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keluhan" class="form-label">Keluhan</label>
                        <textarea class="form-control" id="keluhan" name="keluhan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_servis" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
