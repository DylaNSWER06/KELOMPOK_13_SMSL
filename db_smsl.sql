-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 22 Bulan Mei 2025 pada 12.15
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_smsl`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `kurangi_stok_sparepart` (IN `p_id_servis` INT)   BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE v_id_sparepart INT;
  DECLARE v_qty INT;

  -- Cursor untuk ambil semua sparepart dan qty dari detail_sparepart berdasarkan id_servis
  DECLARE cur CURSOR FOR
    SELECT id_sparepart, qty
    FROM detail_sparepart
    WHERE id_servis = p_id_servis;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO v_id_sparepart, v_qty;

    IF done THEN
      LEAVE read_loop;
    END IF;

    -- Kurangi stok dari tabel sparepart
    UPDATE sparepart
    SET stok = stok - v_qty
    WHERE id_sparepart = v_id_sparepart;
  END LOOP;

  CLOSE cur;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `riwayat_servis_pelanggan` (IN `p_id_pelanggan` INT)   BEGIN
  SELECT s.id_servis, s.tanggal_masuk, s.tanggal_keluar, s.status, s.keluhan,
         t.nama_teknisi
  FROM servis s
  JOIN teknisi t ON s.id_teknisi = t.id_teknisi
  WHERE s.id_pelanggan = p_id_pelanggan;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_pelanggan` (IN `p_nama` VARCHAR(100), IN `p_no_hp` VARCHAR(15), IN `p_email` VARCHAR(100), IN `p_alamat` TEXT)   BEGIN
  INSERT INTO pelanggan (nama_pelanggan, no_hp, email, alamat)
  VALUES (p_nama, p_no_hp, p_email, p_alamat);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_servis` (IN `p_id_pelanggan` INT, IN `p_id_teknisi` INT, IN `p_tanggal_masuk` DATE, IN `p_status` VARCHAR(50), IN `p_keluhan` TEXT)   BEGIN
  INSERT INTO servis (id_pelanggan, id_teknisi, tanggal_masuk, status, keluhan)
  VALUES (p_id_pelanggan, p_id_teknisi, p_tanggal_masuk, p_status, p_keluhan);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_sparepart` (IN `p_nama_part` VARCHAR(100), IN `p_harga` DECIMAL(10,2), IN `p_stok` INT)   BEGIN
  INSERT INTO sparepart (nama_part, harga, stok)
  VALUES (p_nama_part, p_harga, p_stok);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_status_servis` (IN `p_id_servis` INT, IN `p_status_baru` VARCHAR(50), IN `p_tanggal_keluar` DATE)   BEGIN
  UPDATE servis
  SET status = p_status_baru,
      tanggal_keluar = p_tanggal_keluar
  WHERE id_servis = p_id_servis;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_layanan`
--

CREATE TABLE `detail_layanan` (
  `id_detail` int NOT NULL,
  `id_servis` int DEFAULT NULL,
  `id_layanan` int DEFAULT NULL,
  `qty` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `detail_layanan`
--

INSERT INTO `detail_layanan` (`id_detail`, `id_servis`, `id_layanan`, `qty`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 1),
(3, 3, 3, 1),
(4, 4, 4, 1),
(5, 5, 5, 1),
(6, 6, 1, 2),
(7, 7, 2, 1),
(8, 8, 3, 2),
(9, 9, 4, 1),
(10, 10, 5, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_sparepart`
--

CREATE TABLE `detail_sparepart` (
  `id_detail` int NOT NULL,
  `id_servis` int DEFAULT NULL,
  `id_sparepart` int DEFAULT NULL,
  `qty` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `detail_sparepart`
--

INSERT INTO `detail_sparepart` (`id_detail`, `id_servis`, `id_sparepart`, `qty`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 2),
(3, 3, 3, 1),
(4, 4, 4, 1),
(5, 5, 5, 1),
(6, 6, 6, 2),
(7, 7, 7, 1),
(8, 8, 8, 1),
(9, 9, 9, 2),
(10, 10, 10, 1);

--
-- Trigger `detail_sparepart`
--
DELIMITER $$
CREATE TRIGGER `kembalikan_stok_sparepart` AFTER DELETE ON `detail_sparepart` FOR EACH ROW BEGIN
  UPDATE sparepart
  SET stok = stok + OLD.qty
  WHERE id_sparepart = OLD.id_sparepart;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `kurangi_stok_sparepart_insert` AFTER INSERT ON `detail_sparepart` FOR EACH ROW BEGIN
  UPDATE sparepart
  SET stok = stok - NEW.qty
  WHERE id_sparepart = NEW.id_sparepart;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` int NOT NULL,
  `nama_layanan` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `estimasi_hari` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `harga`, `estimasi_hari`) VALUES
(1, 'Ganti Keyboard', 150000.00, 2),
(2, 'Install Ulang', 100000.00, 1),
(3, 'Service Ringan', 100000.00, 2),
(4, 'Service Berat', 250000.00, 4),
(5, 'Perbaikan Kipas', 80000.00, 2),
(6, 'Perbaikan Layar', 150000.00, 3),
(7, 'Penggantian Baterai', 120000.00, 2),
(8, 'Penginstalan Software', 70000.00, 1),
(9, 'Upgrade RAM', 90000.00, 1),
(10, 'Service Keyboard', 60000.00, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int NOT NULL,
  `nama_pelanggan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `no_hp`, `email`, `alamat`) VALUES
(1, 'Andi', '08123456789', 'andi@gmail.com', 'Jl. Melati No. 5'),
(2, 'Agus Wedi', '081234567890', 'agus@mail.com', 'Sampang'),
(3, 'Rina Aprilia', '082345678901', 'rina@mail.com', 'Bangkalan'),
(4, 'Budi Santoso', '083456789012', 'budi@mail.com', 'Surabaya'),
(5, 'Siti Aminah', '084567890123', 'siti@mail.com', 'Pamekasan'),
(6, 'Dedi Prasetyo', '085678901234', 'dedi@mail.com', 'Sumenep'),
(7, 'Lia Kartika', '086789012345', 'lia@mail.com', 'Malang'),
(8, 'Andi Rahman', '087890123456', 'andi@mail.com', 'Jember'),
(9, 'Maya Indah', '088901234567', 'maya@mail.com', 'Probolinggo'),
(10, 'Tono Saputra', '089012345678', 'tono@mail.com', 'Lumajang'),
(11, 'Wulan Sari', '080123456789', 'wulan@mail.com', 'Mojokerto'),
(12, 'Rudi', '081234567890', 'rudi@gmail.com', 'Jl. Mawar 45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `servis`
--

CREATE TABLE `servis` (
  `id_servis` int NOT NULL,
  `id_pelanggan` int DEFAULT NULL,
  `id_teknisi` int DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `keluhan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `servis`
--

INSERT INTO `servis` (`id_servis`, `id_pelanggan`, `id_teknisi`, `tanggal_masuk`, `tanggal_keluar`, `status`, `keluhan`) VALUES
(1, 1, 1, '2024-04-01', '2024-04-05', 'Selesai', 'Mesin berisik'),
(2, 2, 2, '2024-04-02', '2024-04-06', 'Selesai', 'Tidak bisa nyala'),
(3, 3, 3, '2024-04-03', '2024-04-07', 'Proses', 'Kipas rusak'),
(4, 4, 4, '2024-04-04', '2024-04-08', 'Proses', 'Layar mati'),
(5, 5, 5, '2024-04-05', '2024-04-09', 'Selesai', 'Kabel putus'),
(6, 6, 6, '2024-04-06', '2024-04-10', 'Selesai', 'Overheat'),
(7, 7, 7, '2024-04-07', '2024-04-11', 'Selesai', 'Baterai drop'),
(8, 8, 8, '2024-04-08', '2024-04-12', 'Selesai', 'Keyboard error'),
(9, 9, 9, '2024-04-09', '2024-04-13', 'Proses', 'Speaker mati'),
(10, 10, 10, '2024-04-10', '2024-04-14', 'Selesai', 'Software crash');

--
-- Trigger `servis`
--
DELIMITER $$
CREATE TRIGGER `update_tanggal_keluar` BEFORE UPDATE ON `servis` FOR EACH ROW BEGIN
  IF NEW.status = 'Selesai' AND OLD.status <> 'Selesai' THEN
    SET NEW.tanggal_keluar = CURDATE();
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sparepart`
--

CREATE TABLE `sparepart` (
  `id_sparepart` int NOT NULL,
  `nama_part` varchar(100) DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `sparepart`
--

INSERT INTO `sparepart` (`id_sparepart`, `nama_part`, `stok`, `harga`) VALUES
(1, 'Keyboard Laptop ASUS', 10, 200000.00),
(2, 'RAM DDR4 8GB', 5, 450000.00),
(3, 'Keyboard USB', 30, 150000.00),
(4, 'Mouse Wireless', 25, 75000.00),
(5, 'SSD 256GB', 10, 400000.00),
(6, 'RAM 8GB', 12, 350000.00),
(7, 'Motherboard ATX', 5, 1200000.00),
(8, 'Charger Laptop', 18, 200000.00),
(9, 'LCD Screen', 8, 500000.00),
(10, 'Harddisk 1TB', 7, 700000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `teknisi`
--

CREATE TABLE `teknisi` (
  `id_teknisi` int NOT NULL,
  `nama_teknisi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `spesialis` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `teknisi`
--

INSERT INTO `teknisi` (`id_teknisi`, `nama_teknisi`, `spesialis`, `no_hp`) VALUES
(1, 'Budi', 'Hardware', '08129876543'),
(2, 'Sari', 'Software', '081234567002'),
(3, 'Andi', 'Elektronik', '081234567003'),
(4, 'Rina', 'Software', '081234567004'),
(5, 'Tono', 'Hardware', '081234567005'),
(6, 'Dewi', 'Elektronik', '081234567006'),
(7, 'Agus', 'Software', '081234567007'),
(8, 'Maya', 'Hardware', '081234567008'),
(9, 'Joko', 'Elektronik', '081234567009'),
(10, 'Lina', 'Software', '081234567010');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_daftar_layanan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_daftar_layanan` (
`id_layanan` int
,`nama_layanan` varchar(100)
,`harga` decimal(10,2)
,`estimasi_hari` int
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_penggunaan_sparepart`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_penggunaan_sparepart` (
`id_servis` int
,`nama_part` varchar(100)
,`qty` int
,`harga` decimal(10,2)
,`total_harga` decimal(20,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_riwayat_pelanggan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_riwayat_pelanggan` (
`nama_pelanggan` varchar(100)
,`id_servis` int
,`tanggal_masuk` date
,`tanggal_keluar` date
,`status` varchar(50)
,`keluhan` text
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_servis_lengkap`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_servis_lengkap` (
`id_servis` int
,`nama_pelanggan` varchar(100)
,`nama_teknisi` varchar(100)
,`tanggal_masuk` date
,`tanggal_keluar` date
,`status` varchar(50)
,`keluhan` text
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_stok_sparepart`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_stok_sparepart` (
`id_sparepart` int
,`nama_part` varchar(100)
,`harga` decimal(10,2)
,`stok` int
);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_layanan`
--
ALTER TABLE `detail_layanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_servis` (`id_servis`),
  ADD KEY `id_layanan` (`id_layanan`);

--
-- Indeks untuk tabel `detail_sparepart`
--
ALTER TABLE `detail_sparepart`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_servis` (`id_servis`),
  ADD KEY `id_sparepart` (`id_sparepart`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indeks untuk tabel `servis`
--
ALTER TABLE `servis`
  ADD PRIMARY KEY (`id_servis`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_teknisi` (`id_teknisi`);

--
-- Indeks untuk tabel `sparepart`
--
ALTER TABLE `sparepart`
  ADD PRIMARY KEY (`id_sparepart`);

--
-- Indeks untuk tabel `teknisi`
--
ALTER TABLE `teknisi`
  ADD PRIMARY KEY (`id_teknisi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_layanan`
--
ALTER TABLE `detail_layanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `detail_sparepart`
--
ALTER TABLE `detail_sparepart`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id_layanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `servis`
--
ALTER TABLE `servis`
  MODIFY `id_servis` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `sparepart`
--
ALTER TABLE `sparepart`
  MODIFY `id_sparepart` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `teknisi`
--
ALTER TABLE `teknisi`
  MODIFY `id_teknisi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_daftar_layanan`
--
DROP TABLE IF EXISTS `view_daftar_layanan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_daftar_layanan`  AS SELECT `layanan`.`id_layanan` AS `id_layanan`, `layanan`.`nama_layanan` AS `nama_layanan`, `layanan`.`harga` AS `harga`, `layanan`.`estimasi_hari` AS `estimasi_hari` FROM `layanan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_penggunaan_sparepart`
--
DROP TABLE IF EXISTS `view_penggunaan_sparepart`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_penggunaan_sparepart`  AS SELECT `s`.`id_servis` AS `id_servis`, `sp`.`nama_part` AS `nama_part`, `ds`.`qty` AS `qty`, `sp`.`harga` AS `harga`, (`ds`.`qty` * `sp`.`harga`) AS `total_harga` FROM ((`detail_sparepart` `ds` join `sparepart` `sp` on((`ds`.`id_sparepart` = `sp`.`id_sparepart`))) join `servis` `s` on((`ds`.`id_servis` = `s`.`id_servis`))) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_riwayat_pelanggan`
--
DROP TABLE IF EXISTS `view_riwayat_pelanggan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_riwayat_pelanggan`  AS SELECT `p`.`nama_pelanggan` AS `nama_pelanggan`, `s`.`id_servis` AS `id_servis`, `s`.`tanggal_masuk` AS `tanggal_masuk`, `s`.`tanggal_keluar` AS `tanggal_keluar`, `s`.`status` AS `status`, `s`.`keluhan` AS `keluhan` FROM (`pelanggan` `p` join `servis` `s` on((`p`.`id_pelanggan` = `s`.`id_pelanggan`))) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_servis_lengkap`
--
DROP TABLE IF EXISTS `view_servis_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_servis_lengkap`  AS SELECT `s`.`id_servis` AS `id_servis`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `t`.`nama_teknisi` AS `nama_teknisi`, `s`.`tanggal_masuk` AS `tanggal_masuk`, `s`.`tanggal_keluar` AS `tanggal_keluar`, `s`.`status` AS `status`, `s`.`keluhan` AS `keluhan` FROM ((`servis` `s` join `pelanggan` `p` on((`s`.`id_pelanggan` = `p`.`id_pelanggan`))) join `teknisi` `t` on((`s`.`id_teknisi` = `t`.`id_teknisi`))) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_stok_sparepart`
--
DROP TABLE IF EXISTS `view_stok_sparepart`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_stok_sparepart`  AS SELECT `sparepart`.`id_sparepart` AS `id_sparepart`, `sparepart`.`nama_part` AS `nama_part`, `sparepart`.`harga` AS `harga`, `sparepart`.`stok` AS `stok` FROM `sparepart` ;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_layanan`
--
ALTER TABLE `detail_layanan`
  ADD CONSTRAINT `detail_layanan_ibfk_1` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`),
  ADD CONSTRAINT `detail_layanan_ibfk_2` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`);

--
-- Ketidakleluasaan untuk tabel `detail_sparepart`
--
ALTER TABLE `detail_sparepart`
  ADD CONSTRAINT `detail_sparepart_ibfk_1` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`),
  ADD CONSTRAINT `detail_sparepart_ibfk_2` FOREIGN KEY (`id_sparepart`) REFERENCES `sparepart` (`id_sparepart`);

--
-- Ketidakleluasaan untuk tabel `servis`
--
ALTER TABLE `servis`
  ADD CONSTRAINT `servis_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `servis_ibfk_2` FOREIGN KEY (`id_teknisi`) REFERENCES `teknisi` (`id_teknisi`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
