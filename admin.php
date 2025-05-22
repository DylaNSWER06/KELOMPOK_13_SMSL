<?php
session_start();
include 'config/database.php';
include 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah user adalah admin
if ($_SESSION['level'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$success_message = '';
$error_message = '';

// Proses form tambah admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $level = $_POST['level'];
    $status = $_POST['status'];
    
    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "Username sudah digunakan!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Tambahkan admin baru
        $stmt = $conn->prepare("INSERT INTO admin (username, password, nama_lengkap, level, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $hashed_password, $nama_lengkap, $level, $status);
        
        if ($stmt->execute()) {
            $success_message = "Admin berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan admin: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
}

// Proses form edit admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_admin'])) {
    $id_admin = $_POST['id_admin'];
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $level = $_POST['level'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    
    // Cek apakah username sudah ada (kecuali untuk admin yang sedang diedit)
    $check = $conn->prepare("SELECT * FROM admin WHERE username = ? AND id_admin != ?");
    $check->bind_param("si", $username, $id_admin);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "Username sudah digunakan!";
    } else {
        // Jika password diisi, update password juga
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET username = ?, password = ?, nama_lengkap = ?, level = ?, status = ? WHERE id_admin = ?");
            $stmt->bind_param("sssssi", $username, $hashed_password, $nama_lengkap, $level, $status, $id_admin);
        } else {
            // Jika password kosong, jangan update password
            $stmt = $conn->prepare("UPDATE admin SET username = ?, nama_lengkap = ?, level = ?, status = ? WHERE id_admin = ?");
            $stmt->bind_param("ssssi", $username, $nama_lengkap, $level, $status, $id_admin);
        }
        
        if ($stmt->execute()) {
            $success_message = "Data admin berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui data admin: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
}

// Proses hapus admin
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_admin = $_GET['delete'];
    
    // Cek apakah admin yang akan dihapus adalah admin yang sedang login
    if ($id_admin == $_SESSION['user_id']) {
        $error_message = "Anda tidak dapat menghapus akun yang sedang digunakan!";
    } else {
        if ($conn->query("DELETE FROM admin WHERE id_admin = $id_admin")) {
            $success_message = "Admin berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus admin: " . $conn->error;
        }
    }
}

// Ambil data admin
$query = "SELECT * FROM admin ORDER BY id_admin DESC";
$result = $conn->query($query);
$admin_list = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admin_list[] = $row;
    }
}

include 'templates/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Manajemen Admin</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Admin</li>
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
            <i class="fas fa-users-cog me-1"></i>
            Data Admin
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#tambahAdminModal">
                <i class="fas fa-plus"></i> Tambah Admin
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admin_list as $admin): ?>
                        <tr>
                            <td><?= $admin['id_admin'] ?></td>
                            <td><?= $admin['username'] ?></td>
                            <td><?= $admin['nama_lengkap'] ?></td>
                            <td>
                                <span class="badge <?= ($admin['level'] == 'admin') ? 'bg-danger' : (($admin['level'] == 'teknisi') ? 'bg-warning' : 'bg-info') ?>">
                                    <?= ucfirst($admin['level']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= ($admin['status'] == 'aktif') ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($admin['status']) ?>
                                </span>
                            </td>
                            <td><?= $admin['created_at'] ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editAdminModal<?= $admin['id_admin'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($admin['id_admin'] != $_SESSION['user_id']): ?>
                                <a href="admin.php?delete=<?= $admin['id_admin'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus admin ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <!-- Modal Edit Admin -->
                        <div class="modal fade" id="editAdminModal<?= $admin['id_admin'] ?>" tabindex="-1" aria-labelledby="editAdminModalLabel<?= $admin['id_admin'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editAdminModalLabel<?= $admin['id_admin'] ?>">Edit Admin</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_admin" value="<?= $admin['id_admin'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Username</label>
                                                <input type="text" class="form-control" id="username" name="username" value="<?= $admin['username'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $admin['nama_lengkap'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="level" class="form-label">Level</label>
                                                <select class="form-select" id="level" name="level" required>
                                                    <option value="admin" <?= ($admin['level'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                                    <option value="teknisi" <?= ($admin['level'] == 'teknisi') ? 'selected' : '' ?>>Teknisi</option>
                                                    <option value="kasir" <?= ($admin['level'] == 'kasir') ? 'selected' : '' ?>>Kasir</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="aktif" <?= ($admin['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                                    <option value="nonaktif" <?= ($admin['status'] == 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_admin" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Modal Tambah Admin -->
<div class="modal fade" id="tambahAdminModal" tabindex="-1" aria-labelledby="tambahAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahAdminModalLabel">Tambah Admin Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="admin">Admin</option>
                            <option value="teknisi">Teknisi</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_admin" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
