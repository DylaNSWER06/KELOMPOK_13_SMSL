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

// Proses form tambah layanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_layanan'])) {
    $nama_layanan = $_POST['nama_layanan'];
    $harga = $_POST['harga'];
    $estimasi_hari = $_POST['estimasi_hari'];
    
    $stmt = $conn->prepare("INSERT INTO layanan (nama_layanan, harga, estimasi_hari) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $nama_layanan, $harga, $estimasi_hari);
    
    if ($stmt->execute()) {
        $success_message = "Layanan berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan layanan: " . $conn->error;
    }
    $stmt->close();
}

// Proses form edit layanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_layanan'])) {
    $id = $_POST['id_layanan'];
    $nama_layanan = $_POST['nama_layanan'];
    $harga = $_POST['harga'];
    $estimasi_hari = $_POST['estimasi_hari'];
    
    $stmt = $conn->prepare("UPDATE layanan SET nama_layanan = ?, harga = ?, estimasi_hari = ? WHERE id_layanan = ?");
    $stmt->bind_param("sdii", $nama_layanan, $harga, $estimasi_hari, $id);
    
    if ($stmt->execute()) {
        $success_message = "Data layanan berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui data layanan: " . $conn->error;
    }
    $stmt->close();
}

// Proses hapus layanan
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Cek apakah layanan digunakan dalam detail_layanan
    $check = $conn->query("SELECT COUNT(*) as count FROM detail_layanan WHERE id_layanan = $id");
    $row = $check->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Layanan tidak dapat dihapus karena digunakan dalam data servis!";
    } else {
        if ($conn->query("DELETE FROM layanan WHERE id_layanan = $id")) {
            $success_message = "Layanan berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus layanan: " . $conn->error;
        }
    }
}

// Ambil data layanan dari view
$layanan = getDataFromView($conn, 'view_daftar_layanan');

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Data Layanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Layanan</li>
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
            Data Layanan
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahLayananModal">
                <i class="fas fa-plus"></i> Tambah Layanan
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Layanan</th>
                            <th>Harga</th>
                            <th>Estimasi (Hari)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($layanan as $l): ?>
                        <tr>
                            <td><?= $l['id_layanan'] ?></td>
                            <td><?= $l['nama_layanan'] ?></td>
                            <td>Rp <?= number_format($l['harga'], 0, ',', '.') ?></td>
                            <td><?= $l['estimasi_hari'] ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editLayananModal<?= $l['id_layanan'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="layanan.php?delete=<?= $l['id_layanan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Modal Edit Layanan -->
                        <div class="modal fade" id="editLayananModal<?= $l['id_layanan'] ?>" tabindex="-1" aria-labelledby="editLayananModalLabel<?= $l['id_layanan'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editLayananModalLabel<?= $l['id_layanan'] ?>">Edit Layanan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_layanan" value="<?= $l['id_layanan'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nama_layanan" class="form-label">Nama Layanan</label>
                                                <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" value="<?= $l['nama_layanan'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="harga" class="form-label">Harga</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" class="form-control" id="harga" name="harga" value="<?= $l['harga'] ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="estimasi_hari" class="form-label">Estimasi (Hari)</label>
                                                <input type="number" class="form-control" id="estimasi_hari" name="estimasi_hari" value="<?= $l['estimasi_hari'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_layanan" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Modal Tambah Layanan -->
<div class="modal fade" id="tambahLayananModal" tabindex="-1" aria-labelledby="tambahLayananModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahLayananModalLabel">Tambah Layanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_layanan" class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="harga" name="harga" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estimasi_hari" class="form-label">Estimasi (Hari)</label>
                        <input type="number" class="form-control" id="estimasi_hari" name="estimasi_hari" required>
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
