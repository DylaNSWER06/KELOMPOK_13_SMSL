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

// Proses form tambah pelanggan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pelanggan'])) {
    $nama = $_POST['nama'];
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    
    if (tambahPelanggan($conn, $nama, $no_hp, $email, $alamat)) {
        $success_message = "Pelanggan berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan pelanggan: " . $conn->error;
    }
}

// Proses form edit pelanggan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pelanggan'])) {
    $id = $_POST['id_pelanggan'];
    $nama = $_POST['nama'];
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    
    $stmt = $conn->prepare("UPDATE pelanggan SET nama_pelanggan = ?, no_hp = ?, email = ?, alamat = ? WHERE id_pelanggan = ?");
    $stmt->bind_param("ssssi", $nama, $no_hp, $email, $alamat, $id);
    
    if ($stmt->execute()) {
        $success_message = "Data pelanggan berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui data pelanggan: " . $conn->error;
    }
    $stmt->close();
}

// Proses hapus pelanggan
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Cek apakah pelanggan memiliki data servis
    $check = $conn->query("SELECT COUNT(*) as count FROM servis WHERE id_pelanggan = $id");
    $row = $check->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Pelanggan tidak dapat dihapus karena memiliki data servis!";
    } else {
        if ($conn->query("DELETE FROM pelanggan WHERE id_pelanggan = $id")) {
            $success_message = "Pelanggan berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus pelanggan: " . $conn->error;
        }
    }
}

// Ambil data pelanggan
$query = "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC";
$result = $conn->query($query);
$pelanggan = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pelanggan[] = $row;
    }
}

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Data Pelanggan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Pelanggan</li>
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
            Data Pelanggan
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahPelangganModal">
                <i class="fas fa-plus"></i> Tambah Pelanggan
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>No. HP</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pelanggan as $p): ?>
                        <tr>
                            <td><?= $p['id_pelanggan'] ?></td>
                            <td><?= $p['nama_pelanggan'] ?></td>
                            <td><?= $p['no_hp'] ?></td>
                            <td><?= $p['email'] ?></td>
                            <td><?= $p['alamat'] ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editPelangganModal<?= $p['id_pelanggan'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="pelanggan.php?delete=<?= $p['id_pelanggan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="riwayat_pelanggan.php?id=<?= $p['id_pelanggan'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Modal Edit Pelanggan -->
                        <div class="modal fade" id="editPelangganModal<?= $p['id_pelanggan'] ?>" tabindex="-1" aria-labelledby="editPelangganModalLabel<?= $p['id_pelanggan'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editPelangganModalLabel<?= $p['id_pelanggan'] ?>">Edit Pelanggan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_pelanggan" value="<?= $p['id_pelanggan'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nama" class="form-label">Nama Pelanggan</label>
                                                <input type="text" class="form-control" id="nama" name="nama" value="<?= $p['nama_pelanggan'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="no_hp" class="form-label">No. HP</label>
                                                <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= $p['no_hp'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?= $p['email'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="alamat" class="form-label">Alamat</label>
                                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= $p['alamat'] ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_pelanggan" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Modal Tambah Pelanggan -->
<div class="modal fade" id="tambahPelangganModal" tabindex="-1" aria-labelledby="tambahPelangganModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahPelangganModalLabel">Tambah Pelanggan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Pelanggan</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_pelanggan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
