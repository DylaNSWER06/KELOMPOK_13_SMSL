<?php
// Fungsi untuk mendapatkan jumlah total dari tabel
function getTotalCount($conn, $table) {
    $query = "SELECT COUNT(*) as total FROM $table";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Fungsi untuk mendapatkan class badge berdasarkan status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Selesai':
            return 'bg-success';
        case 'Proses':
            return 'bg-warning';
        case 'Menunggu':
            return 'bg-info';
        case 'Batal':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Fungsi untuk menjalankan stored procedure tambah_pelanggan
function tambahPelanggan($conn, $nama, $no_hp, $email, $alamat) {
    $stmt = $conn->prepare("CALL tambah_pelanggan(?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $no_hp, $email, $alamat);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk menjalankan stored procedure tambah_servis
function tambahServis($conn, $id_pelanggan, $id_teknisi, $tanggal_masuk, $status, $keluhan) {
    $stmt = $conn->prepare("CALL tambah_servis(?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $id_pelanggan, $id_teknisi, $tanggal_masuk, $status, $keluhan);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk menjalankan stored procedure tambah_sparepart
function tambahSparepart($conn, $nama_part, $harga, $stok) {
    $stmt = $conn->prepare("CALL tambah_sparepart(?, ?, ?)");
    $stmt->bind_param("sdi", $nama_part, $harga, $stok);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk menjalankan stored procedure update_status_servis
function updateStatusServis($conn, $id_servis, $status_baru, $tanggal_keluar) {
    $stmt = $conn->prepare("CALL update_status_servis(?, ?, ?)");
    $stmt->bind_param("iss", $id_servis, $status_baru, $tanggal_keluar);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk menjalankan stored procedure riwayat_servis_pelanggan
function getRiwayatServisPelanggan($conn, $id_pelanggan) {
    $result = $conn->query("CALL riwayat_servis_pelanggan($id_pelanggan)");
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        // Tutup result set
        $result->close();
        // Kosongkan result sets tambahan
        while ($conn->more_results()) {
            $conn->next_result();
            if ($res = $conn->store_result()) {
                $res->free();
            }
        }
    }
    return $data;
}

// Fungsi untuk menambahkan detail sparepart (akan memicu trigger)
function tambahDetailSparepart($conn, $id_servis, $id_sparepart, $qty) {
    $stmt = $conn->prepare("INSERT INTO detail_sparepart (id_servis, id_sparepart, qty) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_servis, $id_sparepart, $qty);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk menambahkan detail layanan
function tambahDetailLayanan($conn, $id_servis, $id_layanan, $qty) {
    $stmt = $conn->prepare("INSERT INTO detail_layanan (id_servis, id_layanan, qty) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_servis, $id_layanan, $qty);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk menghapus detail sparepart (akan memicu trigger)
function hapusDetailSparepart($conn, $id_detail) {
    $stmt = $conn->prepare("DELETE FROM detail_sparepart WHERE id_detail = ?");
    $stmt->bind_param("i", $id_detail);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Fungsi untuk mendapatkan data dari view
function getDataFromView($conn, $view_name, $limit = null, $where = null) {
    $query = "SELECT * FROM $view_name";
    
    if ($where) {
        $query .= " WHERE $where";
    }
    
    if ($limit) {
        $query .= " LIMIT $limit";
    }
    
    $result = $conn->query($query);
    $data = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    return $data;
}

// Fungsi untuk menjalankan stored procedure analisis_penggunaan_sparepart
function getAnalisisPenggunaanSparepart($conn, $tanggal_awal, $tanggal_akhir) {
    $stmt = $conn->prepare("CALL analisis_penggunaan_sparepart(?, ?)");
    $stmt->bind_param("ss", $tanggal_awal, $tanggal_akhir);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    $stmt->close();
    
    // Kosongkan result sets tambahan
    while ($conn->more_results()) {
        $conn->next_result();
        if ($res = $conn->store_result()) {
            $res->free();
        }
    }
    
    return $data;
}
?>
