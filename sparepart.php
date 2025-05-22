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

// Proses form tambah sparepart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_sparepart'])) {
    $nama_part = $_POST['nama_part'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    if (tambahSparepart($conn, $nama_part, $harga, $stok)) {
        $success_message = "Sparepart berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan sparepart: " . $conn->error;
    }
}

// Proses form edit sparepart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_sparepart'])) {
    $id = $_POST['id_sparepart'];
    $nama_part = $_POST['nama_part'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    $stmt = $conn->prepare("UPDATE sparepart SET nama_part = ?, harga = ?, stok = ? WHERE id_sparepart = ?");
    $stmt->bind_param("sdii", $nama_part, $harga, $stok, $id);
    
    if ($stmt->execute()) {
        $success_message = "Data sparepart berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui data sparepart: " . $conn->error;
    }
    $stmt->close();
}

// Proses hapus sparepart
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Cek apakah sparepart digunakan dalam detail_sparepart
    $check = $conn->query("SELECT COUNT(*) as count FROM detail_sparepart WHERE id_sparepart = $id");
    $row = $check->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Sparepart tidak dapat dihapus karena digunakan dalam data servis!";
    } else {
        if ($conn->query("DELETE FROM sparepart WHERE id_sparepart = $id")) {
            $success_message = "Sparepart berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus sparepart: " . $conn->error;
        }
    }
}

// Ambil data sparepart dari view
$sparepart = getDataFromView($conn, 'view_stok_sparepart');

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Data Sparepart</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Sparepart</li>
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
            Data Sparepart
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahSparepartModal">
                <i class="fas fa-plus"></i> Tambah Sparepart
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Part</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sparepart as $s): ?>
                        <tr>
                            <td><?= $s['id_sparepart'] ?></td>
                            <td><?= $s['nama_part'] ?></td>
                            <td>Rp <?= number_format($s['harga'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge <?= ($s['stok'] <= 5) ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $s['stok'] ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editSparepartModal<?= $s['id_sparepart'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="sparepart.php?delete=<?= $s['id_sparepart'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus sparepart ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Modal Edit Sparepart -->
                        <div class="modal fade" id="editSparepartModal<?= $s['id_sparepart'] ?>" tabindex="-1" aria-labelledby="editSparepartModalLabel<?= $s['id_sparepart'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editSparepartModalLabel<?= $s['id_sparepart'] ?>">Edit Sparepart</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_sparepart" value="<?= $s['id_sparepart'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nama_part" class="form-label">Nama Part</label>
                                                <input type="text" class="form-control" id="nama_part" name="nama_part" value="<?= $s['nama_part'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="harga" class="form-label">Harga</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" class="form-control" id="harga" name="harga" value="<?= $s['harga'] ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="stok" class="form-label">Stok</label>
                                                <input type="number" class="form-control" id="stok" name="stok" value="<?= $s['stok'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_sparepart" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Modal Tambah Sparepart -->
<div class="modal fade" id="tambahSparepartModal" tabindex="-1" aria-labelledby="tambahSparepartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahSparepartModalLabel">Tambah Sparepart Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_part" class="form-label">Nama Part</label>
                        <input type="text" class="form-control" id="nama_part" name="nama_part" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="harga" name="harga" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" required>
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

<?php include 'templates/footer.php'; ?>
