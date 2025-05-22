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

// Proses form tambah teknisi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_teknisi'])) {
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $no_hp = $_POST['no_hp'];
    
    $stmt = $conn->prepare("INSERT INTO teknisi (nama_teknisi, spesialis, no_hp) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $spesialis, $no_hp);
    
    if ($stmt->execute()) {
        $success_message = "Teknisi berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan teknisi: " . $conn->error;
    }
    $stmt->close();
}

// Proses form edit teknisi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_teknisi'])) {
    $id = $_POST['id_teknisi'];
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $no_hp = $_POST['no_hp'];
    
    $stmt = $conn->prepare("UPDATE teknisi SET nama_teknisi = ?, spesialis = ?, no_hp = ? WHERE id_teknisi = ?");
    $stmt->bind_param("sssi", $nama, $spesialis, $no_hp, $id);
    
    if ($stmt->execute()) {
        $success_message = "Data teknisi berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui data teknisi: " . $conn->error;
    }
    $stmt->close();
}

// Proses hapus teknisi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Cek apakah teknisi memiliki data servis
    $check = $conn->query("SELECT COUNT(*) as count FROM servis WHERE id_teknisi = $id");
    $row = $check->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Teknisi tidak dapat dihapus karena memiliki data servis!";
    } else {
        if ($conn->query("DELETE FROM teknisi WHERE id_teknisi = $id")) {
            $success_message = "Teknisi berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus teknisi: " . $conn->error;
        }
    }
}

// Ambil data teknisi
$query = "SELECT * FROM teknisi ORDER BY id_teknisi DESC";
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
    <h1 class="mt-4">Data Teknisi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Teknisi</li>
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
            Data Teknisi
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahTeknisiModal">
                <i class="fas fa-plus"></i> Tambah Teknisi
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Teknisi</th>
                            <th>Spesialis</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teknisi as $t): ?>
                        <tr>
                            <td><?= $t['id_teknisi'] ?></td>
                            <td><?= $t['nama_teknisi'] ?></td>
                            <td><?= $t['spesialis'] ?></td>
                            <td><?= $t['no_hp'] ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editTeknisiModal<?= $t['id_teknisi'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="teknisi.php?delete=<?= $t['id_teknisi'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus teknisi ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Modal Edit Teknisi -->
                        <div class="modal fade" id="editTeknisiModal<?= $t['id_teknisi'] ?>" tabindex="-1" aria-labelledby="editTeknisiModalLabel<?= $t['id_teknisi'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editTeknisiModalLabel<?= $t['id_teknisi'] ?>">Edit Teknisi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_teknisi" value="<?= $t['id_teknisi'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nama" class="form-label">Nama Teknisi</label>
                                                <input type="text" class="form-control" id="nama" name="nama" value="<?= $t['nama_teknisi'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="spesialis" class="form-label">Spesialis</label>
                                                <select class="form-select" id="spesialis" name="spesialis" required>
                                                    <option value="Hardware" <?= ($t['spesialis'] == 'Hardware') ? 'selected' : '' ?>>Hardware</option>
                                                    <option value="Software" <?= ($t['spesialis'] == 'Software') ? 'selected' : '' ?>>Software</option>
                                                    <option value="Elektronik" <?= ($t['spesialis'] == 'Elektronik') ? 'selected' : '' ?>>Elektronik</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="no_hp" class="form-label">No. HP</label>
                                                <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= $t['no_hp'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_teknisi" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Modal Tambah Teknisi -->
<div class="modal fade" id="tambahTeknisiModal" tabindex="-1" aria-labelledby="tambahTeknisiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahTeknisiModalLabel">Tambah Teknisi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Teknisi</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="spesialis" class="form-label">Spesialis</label>
                        <select class="form-select" id="spesialis" name="spesialis" required>
                            <option value="" selected disabled>Pilih Spesialis</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Elektronik">Elektronik</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_teknisi" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
