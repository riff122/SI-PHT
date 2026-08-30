-- =======================================================
-- Database: ternakapp (Sistem Informasi Penjualan Hewan Ternak / SI-PHT)
-- =======================================================

CREATE DATABASE IF NOT EXISTS `ternakapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ternakapp`;

-- --------------------------------------------------------
-- Tabel: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `wa` varchar(25) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','peternak') NOT NULL DEFAULT 'peternak',
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabel: hewan
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hewan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nama_hewan` varchar(150) NOT NULL,
  `ras` varchar(100) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `berat` decimal(8,2) NOT NULL DEFAULT 0.00,
  `satuan_berat` varchar(20) NOT NULL DEFAULT 'kg',
  `umur` varchar(100) DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deskripsi` text DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('Tersedia','Terjual') NOT NULL DEFAULT 'Tersedia',
  `status_arsip` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_terjual` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hewan_user` (`user_id`),
  KEY `idx_hewan_status` (`status`, `status_arsip`),
  CONSTRAINT `fk_hewan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabel: hewan_foto
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hewan_foto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hewan_id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_foto_hewan` (`hewan_id`),
  CONSTRAINT `fk_foto_hewan` FOREIGN KEY (`hewan_id`) REFERENCES `hewan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabel: pengeluaran
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hewan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_pengeluaran` varchar(150) NOT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengeluaran_hewan` (`hewan_id`),
  KEY `idx_pengeluaran_user` (`user_id`),
  CONSTRAINT `fk_pengeluaran_hewan` FOREIGN KEY (`hewan_id`) REFERENCES `hewan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pengeluaran_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Data Awal / Akun Demo Default
-- Password default akun: admin123 / peternak123
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nama`, `email`, `wa`, `alamat`, `desa`, `password`, `role`, `status`, `foto`, `last_login`, `created_at`) VALUES
(1, 'Administrator', 'admin@ternakapp.id', '081234567890', 'Kemalang, Klaten', 'Keputran', '$2y$10$wO0QcZgZ2K/HwUoYvjJdLeCgT4ZkO3r/kZ8S9aO1r.0W7qK8k8k8e', 'admin', 'aktif', NULL, NULL, NOW()),
(2, 'Pak Budi Santoso', 'peternak@ternakapp.id', '081298765432', 'Tasikmadu, Kemalang, Klaten', 'Tasikmadu', '$2y$10$wO0QcZgZ2K/HwUoYvjJdLeCgT4ZkO3r/kZ8S9aO1r.0W7qK8k8k8e', 'peternak', 'aktif', NULL, NULL, NOW())
ON DUPLICATE KEY UPDATE `id`=`id`;
