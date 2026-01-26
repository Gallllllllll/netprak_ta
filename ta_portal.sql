-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 09, 2026 at 04:07 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ta_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`, `email`, `created_at`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Admin1', 'admin@kampus.ac.id', '2026-01-07 07:17:24'),
(2, 'adminta', '$2y$10$FdGv7FVAKkhTtRsQeb5c.OxtM8VCEgZReqcwAE70AUx0OFMBynjBS', 'admin2', 'adminta@gmail.com', '2026-01-07 07:34:23'),
(3, 'admin3', '$2y$10$zE455OcJHrOv7QOU8eDvl.PrKNpFbNSbZE60S3Xp1lUTequUi34kK', 'admin3', 'admin3@kampus.ac.id', '2026-01-09 01:47:59');

-- --------------------------------------------------------

--
-- Table structure for table `dosbing_ta`
--

CREATE TABLE `dosbing_ta` (
  `id` int NOT NULL,
  `pengajuan_id` int NOT NULL,
  `dosen_id` int NOT NULL,
  `role` enum('dosbing_1','dosbing_2') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dosbing_ta`
--

INSERT INTO `dosbing_ta` (`id`, `pengajuan_id`, `dosen_id`, `role`, `created_at`) VALUES
(1, 1, 1, 'dosbing_1', '2026-01-09 03:06:37'),
(2, 1, 2, 'dosbing_2', '2026-01-09 03:06:37'),
(3, 1, 1, 'dosbing_1', '2026-01-09 03:06:51'),
(4, 1, 2, 'dosbing_2', '2026-01-09 03:06:51'),
(5, 2, 1, 'dosbing_1', '2026-01-09 04:00:55'),
(6, 2, 2, 'dosbing_2', '2026-01-09 04:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int NOT NULL,
  `nip` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `nip`, `username`, `password`, `nama`, `email`, `created_at`) VALUES
(1, '1978123456789001', 'dosen1', '6a43336baf50915c0042ba1ccecc7c75072763569bf8ad735bd7f6b4419ceb67', 'Dosen 1', 'dosen1@kampus.ac.id', '2026-01-07 07:18:16'),
(2, '12345', 'dosen2', '$2y$10$FUVacflVK6BExzKUVWmq4uPnxSgVfCGjCODYUOacb1ldOAEJ01I/O', 'dosen2', 'dosen2@kampus.ac.id', '2026-01-07 08:22:39');

-- --------------------------------------------------------

--
-- Table structure for table `dosen_pembimbing`
--

CREATE TABLE `dosen_pembimbing` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `dosen1_id` int NOT NULL,
  `dosen2_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `prodi` varchar(50) DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `nomor_telepon` varchar(25) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `username`, `password`, `nama`, `email`, `prodi`, `kelas`, `nomor_telepon`, `created_at`) VALUES
(1, '123456', 'mhs1', 'eb67db6839be4d7309b3a070101dc9756e443291a945df9d0b8afa433678cb44', 'Mahasiswa 1', 'mhs1@kampus.ac.id', 'Teknik Informatika', 'TI-1', '0856123456', '2026-01-07 07:17:50'),
(2, '1234', 'mhs2', '$2y$10$F.rw/wiaxcGTnUfx2bA04eCDwOioc7YsgOIgBMUkn/WErDbJKjIOS', 'Mahasiswa 2', 'mhs2@kampus.ac.id', 'Seni Kuliner', 'mhs2', '12345678910', '2026-01-07 07:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_ta`
--

CREATE TABLE `pengajuan_ta` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `judul_ta` varchar(255) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `formulir_pendaftaran` varchar(255) DEFAULT NULL,
  `transkrip_nilai` varchar(255) DEFAULT NULL,
  `bukti_magang` varchar(255) DEFAULT NULL,
  `status` enum('proses','revisi','ditolak','disetujui') DEFAULT 'proses',
  `catatan_admin` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_bukti_pembayaran` enum('proses','revisi','ditolak','disetujui') DEFAULT 'proses',
  `catatan_bukti_pembayaran` text,
  `status_formulir_pendaftaran` enum('proses','revisi','ditolak','disetujui') DEFAULT 'proses',
  `catatan_formulir_pendaftaran` text,
  `status_transkrip_nilai` enum('proses','revisi','ditolak','disetujui') DEFAULT 'proses',
  `catatan_transkrip_nilai` text,
  `status_bukti_magang` enum('proses','revisi','ditolak','disetujui') DEFAULT 'proses',
  `catatan_bukti_magang` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengajuan_ta`
--

INSERT INTO `pengajuan_ta` (`id`, `mahasiswa_id`, `judul_ta`, `bukti_pembayaran`, `formulir_pendaftaran`, `transkrip_nilai`, `bukti_magang`, `status`, `catatan_admin`, `created_at`, `status_bukti_pembayaran`, `catatan_bukti_pembayaran`, `status_formulir_pendaftaran`, `catatan_formulir_pendaftaran`, `status_transkrip_nilai`, `catatan_transkrip_nilai`, `status_bukti_magang`, `catatan_bukti_magang`) VALUES
(1, 2, 'uji coba', '1767927027_form_pendaftaran_persetujuan_tema_ta.pdf', '1767927027_form_pendaftaran_persetujuan_tema_ta.pdf', '1767927027_form_pendaftaran_persetujuan_tema_ta.pdf', '1767927027_form_pendaftaran_persetujuan_tema_ta.pdf', 'disetujui', '', '2026-01-09 02:50:27', 'proses', NULL, 'proses', NULL, 'proses', NULL, 'proses', NULL),
(2, 2, 'COBA YA', '1767931218_Surat_Balasan_UNS.pdf', '1767931035_form_pendaftaran_persetujuan_tema_ta.pdf', '1767931035_form_pendaftaran_persetujuan_tema_ta.pdf', '1767931035_form_pendaftaran_persetujuan_tema_ta.pdf', 'disetujui', NULL, '2026-01-09 03:15:19', 'disetujui', '', 'disetujui', '', 'disetujui', '', 'disetujui', '');

-- --------------------------------------------------------

--
-- Table structure for table `template_dokumen`
--

CREATE TABLE `template_dokumen` (
  `id` int NOT NULL,
  `nama_template` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `dosbing_ta`
--
ALTER TABLE `dosbing_ta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`),
  ADD KEY `dosen_id` (`dosen_id`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `dosen1_id` (`dosen1_id`),
  ADD KEY `dosen2_id` (`dosen2_id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `pengajuan_ta`
--
ALTER TABLE `pengajuan_ta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengajuan_mahasiswa` (`mahasiswa_id`);

--
-- Indexes for table `template_dokumen`
--
ALTER TABLE `template_dokumen`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dosbing_ta`
--
ALTER TABLE `dosbing_ta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengajuan_ta`
--
ALTER TABLE `pengajuan_ta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `template_dokumen`
--
ALTER TABLE `template_dokumen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dosbing_ta`
--
ALTER TABLE `dosbing_ta`
  ADD CONSTRAINT `dosbing_ta_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_ta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dosbing_ta_ibfk_2` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  ADD CONSTRAINT `dosen_pembimbing_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`),
  ADD CONSTRAINT `dosen_pembimbing_ibfk_2` FOREIGN KEY (`dosen1_id`) REFERENCES `dosen` (`id`),
  ADD CONSTRAINT `dosen_pembimbing_ibfk_3` FOREIGN KEY (`dosen2_id`) REFERENCES `dosen` (`id`);

--
-- Constraints for table `pengajuan_ta`
--
ALTER TABLE `pengajuan_ta`
  ADD CONSTRAINT `fk_pengajuan_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
