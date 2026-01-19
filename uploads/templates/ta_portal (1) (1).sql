-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 14, 2026 at 08:22 AM
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
  `nip` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `nip`, `password`, `nama`, `email`, `created_at`) VALUES
(1, 'admin', '1987654321', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Admin1', 'admin@kampus.ac.id', '2026-01-07 07:17:24'),
(2, 'adminta', '1990123456', '$2y$10$FdGv7FVAKkhTtRsQeb5c.OxtM8VCEgZReqcwAE70AUx0OFMBynjBS', 'admin2', 'adminta@gmail.com', '2026-01-07 07:34:23'),
(3, 'admin3', '1999988888', '$2y$10$zE455OcJHrOv7QOU8eDvl.PrKNpFbNSbZE60S3Xp1lUTequUi34kK', 'admin3', 'admin3@kampus.ac.id', '2026-01-09 01:47:59'),
(4, 'admin1', '197586235', '$2y$10$fQrq9K.Wej9iVWPVTqoHbe6qtDzJbnGZq1URfDXJa2okqrZNMr8m6', 'Admin 1', 'admin1@gmail.com', '2026-01-12 02:07:31');

-- --------------------------------------------------------

--
-- Table structure for table `dosbing_ta`
--

CREATE TABLE `dosbing_ta` (
  `id` int NOT NULL,
  `pengajuan_id` int NOT NULL,
  `dosen_id` int NOT NULL,
  `role` enum('dosbing_1','dosbing_2') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `persetujuan_sempro` varchar(255) DEFAULT NULL,
  `status_persetujuan` enum('pending','disetujui') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dosbing_ta`
--

INSERT INTO `dosbing_ta` (`id`, `pengajuan_id`, `dosen_id`, `role`, `created_at`, `persetujuan_sempro`, `status_persetujuan`) VALUES
(7, 3, 1, 'dosbing_1', '2026-01-12 02:13:42', 'persetujuan_sempro_7_1768187491.pdf', 'disetujui'),
(8, 3, 2, 'dosbing_2', '2026-01-12 02:13:42', 'persetujuan_sempro_8_1768187532.pdf', 'disetujui'),
(9, 5, 3, 'dosbing_1', '2026-01-12 04:41:10', 'persetujuan_sempro_9_1768207081.pdf', 'disetujui'),
(10, 5, 5, 'dosbing_2', '2026-01-12 04:41:10', NULL, 'pending'),
(11, 6, 3, 'dosbing_1', '2026-01-14 04:10:00', 'persetujuan_sempro_11_1768363940.pdf', 'disetujui'),
(12, 6, 5, 'dosbing_2', '2026-01-14 04:10:00', 'persetujuan_sempro_12_1768363926.pdf', 'disetujui');

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
(1, '1978123456789001', 'dosen1', '$2y$10$B10ZFexq3zJRwsUVBH8Y3uLNZsUmvl7kAPQUTbvwEMGnAUTkOBCfq', 'dosen1', 'dosen1@kampus.ac.id', '2026-01-07 07:18:16'),
(2, '12345', 'dosen2', '$2y$10$9XD832cdIaBzoSyvmw5N1.7N4Zaf1Soy7iAFw/yA0dn00Wij/Jydi', 'dosen2', 'dosen2@kampus.ac.id', '2026-01-07 08:22:39'),
(3, '20050101', 'Alfia', '$2y$10$C2WCFJpljpgj88WlBsVOBunGxbaIz9DLKOu4FYd/dsub8EwmWtarC', 'Alfia', 'alfia@kampus.ac.id', '2026-01-12 04:37:27'),
(4, '20050102', 'Asshyari', '$2y$10$SnkGKtHjXwM6I.E8kJ61eutMleE1tMKrUkmhjIaIpXJJ5PZHn8wfC', 'Asshyari', 'asshyari@kampus.ac.id', '2026-01-12 04:37:55'),
(5, '20050103', 'Najela', '$2y$10$KKZQv2gLnfi.V.EfT5S8L.dmQFvk5ehdnbiCG9jQvcexx2OlYmoYW', 'Najela', 'najela@kampus.ac.id', '2026-01-12 04:39:56'),
(6, '20050104', 'Ananias', '$2y$10$szgSC30oXywQk9ozmKHMse0aALNBx9QN.ITrhoVOX22KY73eH3ciS', 'Ananias', 'ananias@kampus.ac.id', '2026-01-12 04:40:19'),
(7, '20050105', 'Fatira', '$2y$10$1xaSMFaKxV8Zrs8GVXofbu1pWcl4vca2iUbHiVuqj603bpt.afN9u', 'Fatira', 'fatira@kampus.ac.id', '2026-01-12 04:40:44');

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
(1, '123456', 'mhs1', '$2y$10$fGf7SEcKyp/crmk3upZ5g.kDB/OXHV1fyZMlXB5.GXzuJYRZvMyHS', 'Mahasiswa 1', 'mhs1@kampus.ac.id', 'Seni Kuliner', 'SK-2', '0856123456', '2026-01-07 07:17:50'),
(2, '1234', 'mhs2', '$2y$10$F.rw/wiaxcGTnUfx2bA04eCDwOioc7YsgOIgBMUkn/WErDbJKjIOS', 'Mahasiswa 2', 'mhs2@kampus.ac.id', 'Seni Kuliner', 'mhs2', '12345678910', '2026-01-07 07:57:16'),
(4, '20050101', 'galih', '$2y$10$DYc2XKw0sFNHix0qIXFCo.7T7W2WnaTxjMC0MvKlVppx8YUDK.EeK', 'Galih Ananias', 'galihlagi@gmail.com', 'Teknologi Informasi', 'TI1', '085606666', '2026-01-14 03:38:11'),
(8, '12345609', 'mhs9', '$2y$10$1epGZnZlwBeLbNCYowQAcemvk/Y/GCxMLEVWZMNVptIUxJ6C7VlnK', 'Mahasiswa 9', 'mhs9@kampus.ac.id', 'Seni Kuliner', 'SK-1', '123456', '2026-01-14 08:11:11'),
(9, '12345610', 'mhs10', '$2y$10$L8oVB4FMolUy5cAgKa6sduEXaTlu15W8iL2.hwdfVrHH9MFvPvXgS', 'Mahasiswa 10', 'mhs10@kampus.ac.id', 'Teknologi Informasi', 'TI-1', '123465', '2026-01-14 08:11:11'),
(10, '12345611', 'mhs11', '$2y$10$1oisADoiIAmQi9RUedk5peWAJtC43zSCoi8eX3zQ./BiUPVDcAql2', 'Mahasiswa 11', 'mhs11@kampus.ac.id', 'Perhotelan', 'PH-1', '123475', '2026-01-14 08:11:11'),
(11, '12345612', 'mhs12', '$2y$10$C.pKqMd/GO/Z.ZXBFziq0eh/vRKLMSACy2O43JFndZcXo4wXS.sN2', 'Mahasiswa 12', 'mhs10@kampus.ac.id', 'Seni Kuliner', 'SK-1', '123456', '2026-01-14 08:22:03'),
(12, '12345613', 'mhs13', '$2y$10$qv0j8XpwX4SQwciKELkxw.YQqW6QTvMzP0G8WcjScwSNBvxW99Wt6', 'Mahasiswa 13', 'mhs13@kampus.ac.id', 'Teknologi Informasi', 'TI-1', '123465', '2026-01-14 08:22:03'),
(13, '12345614', 'mhs14', '$2y$10$ntTrvGTdDiQxChs02bM9aOEg0B4cf7nCqIP630J.1QhcC3S.xAmsK', 'Mahasiswa 14', 'mhs14@kampus.ac.id', 'Perhotelan', 'PH-1', '123475', '2026-01-14 08:22:03');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_semhas`
--

CREATE TABLE `pengajuan_semhas` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `pengajuan_ta_id` int NOT NULL,
  `file_berita_acara` varchar(255) DEFAULT NULL,
  `file_persetujuan_laporan` varchar(255) DEFAULT NULL,
  `file_pendaftaran_ujian` varchar(255) DEFAULT NULL,
  `file_buku_konsultasi` varchar(255) DEFAULT NULL,
  `status` enum('diajukan','revisi','disetujui','ditolak') DEFAULT 'diajukan',
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_file_berita_acara` enum('pending','valid','revisi') DEFAULT 'pending',
  `catatan_file_berita_acara` text,
  `status_file_persetujuan_laporan` enum('pending','valid','revisi') DEFAULT 'pending',
  `catatan_file_persetujuan_laporan` text,
  `status_file_pendaftaran_ujian` enum('pending','valid','revisi') DEFAULT 'pending',
  `catatan_file_pendaftaran_ujian` text,
  `status_file_buku_konsultasi` enum('pending','valid','revisi') DEFAULT 'pending',
  `catatan_file_buku_konsultasi` text,
  `tanggal_sidang` date DEFAULT NULL,
  `jam_sidang` time DEFAULT NULL,
  `tempat_sidang` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengajuan_semhas`
--

INSERT INTO `pengajuan_semhas` (`id`, `mahasiswa_id`, `pengajuan_ta_id`, `file_berita_acara`, `file_persetujuan_laporan`, `file_pendaftaran_ujian`, `file_buku_konsultasi`, `status`, `catatan`, `created_at`, `status_file_berita_acara`, `catatan_file_berita_acara`, `status_file_persetujuan_laporan`, `catatan_file_persetujuan_laporan`, `status_file_pendaftaran_ujian`, `catatan_file_pendaftaran_ujian`, `status_file_buku_konsultasi`, `catatan_file_buku_konsultasi`, `tanggal_sidang`, `jam_sidang`, `tempat_sidang`) VALUES
(1, 2, 3, '1768360523_Surat_Balasan_UNS.pdf', '1768359120_file_persetujuan_laporan_Surat_Balasan_UNS.pdf', '1768359120_file_pendaftaran_ujian_Surat_Balasan_UNS.pdf', '1768359120_file_buku_konsultasi_Surat_Balasan_UNS.pdf', 'disetujui', NULL, '2026-01-14 02:52:00', 'valid', '', 'valid', '', 'valid', '', 'valid', '', '2026-01-30', '09:00:00', 'Ruang Sidang 1'),
(2, 4, 6, '1768371219_Surat_Balasan_UNS.pdf', '1768366794_file_persetujuan_laporan_Surat_Balasan_UNS.pdf', '1768366794_file_pendaftaran_ujian_Surat_Balasan_UNS.pdf', '1768366794_file_buku_konsultasi_Surat_Balasan_UNS.pdf', 'disetujui', NULL, '2026-01-14 04:59:54', 'valid', '', 'valid', '', 'valid', '', 'valid', '', '2026-01-31', '13:13:00', 'online');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_sempro`
--

CREATE TABLE `pengajuan_sempro` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `pengajuan_ta_id` int NOT NULL,
  `file_pendaftaran` varchar(255) NOT NULL,
  `file_persetujuan` varchar(255) NOT NULL,
  `file_buku_konsultasi` varchar(255) NOT NULL,
  `status` enum('diajukan','disetujui','revisi','ditolak') DEFAULT 'diajukan',
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_file_pendaftaran` varchar(20) DEFAULT 'proses',
  `catatan_file_pendaftaran` text,
  `status_file_persetujuan` varchar(20) DEFAULT 'proses',
  `catatan_file_persetujuan` text,
  `status_file_buku_konsultasi` varchar(20) DEFAULT 'proses',
  `catatan_file_buku_konsultasi` text,
  `tanggal_sempro` date DEFAULT NULL,
  `jam_sempro` time DEFAULT NULL,
  `ruangan_sempro` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengajuan_sempro`
--

INSERT INTO `pengajuan_sempro` (`id`, `mahasiswa_id`, `pengajuan_ta_id`, `file_pendaftaran`, `file_persetujuan`, `file_buku_konsultasi`, `status`, `catatan`, `created_at`, `status_file_pendaftaran`, `catatan_file_pendaftaran`, `status_file_persetujuan`, `catatan_file_persetujuan`, `status_file_buku_konsultasi`, `catatan_file_buku_konsultasi`, `tanggal_sempro`, `jam_sempro`, `ruangan_sempro`) VALUES
(1, 2, 3, 'pendaftaran_1768188198_838.pdf', 'persetujuan_1768188198_725.pdf', 'konsultasi_1768188198_142.pdf', 'disetujui', NULL, '2026-01-12 03:23:18', 'disetujui', '', 'disetujui', '', 'disetujui', '', NULL, NULL, NULL),
(2, 4, 6, '1768365444_Surat_Balasan_UNS.pdf', 'persetujuan_1768363967_411.pdf', 'konsultasi_1768363967_926.pdf', 'disetujui', NULL, '2026-01-14 04:12:47', 'disetujui', '', 'disetujui', '', 'disetujui', '', '2026-01-23', '15:46:00', 'Ruang Sidang 1');

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
(3, 2, 'COBA YA', '1768182512_Surat_Balasan_UNS.pdf', '1768182512_Surat_Balasan_UNS.pdf', '1768182512_Surat_Balasan_UNS.pdf', '1768182512_Surat_Balasan_UNS.pdf', 'disetujui', NULL, '2026-01-12 01:48:32', 'disetujui', '', 'disetujui', '', 'disetujui', '', 'disetujui', ''),
(5, 1, 'INI MAHASISWA 1', '1768192486_form_pendaftaran_persetujuan_tema_ta.pdf', '1768192486_form_pendaftaran_persetujuan_tema_ta.pdf', '1768190077_Surat_Balasan_UNS.pdf', '1768190077_Surat_Balasan_UNS.pdf', 'disetujui', NULL, '2026-01-12 03:54:37', 'disetujui', '', 'disetujui', '', 'disetujui', '', 'disetujui', ''),
(6, 4, 'Top 10 Things to Do in Surakarta', '1768363572_Surat_Balasan_UNS.pdf', '1768361969_Surat_Balasan_UNS.pdf', '1768361969_Surat_Balasan_UNS.pdf', '1768361969_Surat_Balasan_UNS.pdf', 'disetujui', NULL, '2026-01-14 03:39:29', 'disetujui', '', 'disetujui', '', 'disetujui', '', 'disetujui', '');

-- --------------------------------------------------------

--
-- Table structure for table `revisi_ta`
--

CREATE TABLE `revisi_ta` (
  `id` int NOT NULL,
  `pengajuan_id` int NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `revisi_ta`
--

INSERT INTO `revisi_ta` (`id`, `pengajuan_id`, `nama_file`, `catatan`, `created_at`) VALUES
(1, 5, '1768190077_Surat_Balasan_UNS.pdf', '', '2026-01-12 04:20:21'),
(2, 5, '1768192486_form_pendaftaran_persetujuan_tema_ta.pdf', NULL, '2026-01-12 04:34:46'),
(3, 5, '1768192486_form_pendaftaran_persetujuan_tema_ta.pdf', NULL, '2026-01-12 04:34:46'),
(5, 6, '1768363308_Surat_Balasan_UNS.pdf', NULL, '2026-01-14 04:01:48'),
(6, 6, '1768363572_Surat_Balasan_UNS.pdf', NULL, '2026-01-14 04:06:12');

-- --------------------------------------------------------

--
-- Table structure for table `template`
--

CREATE TABLE `template` (
  `id` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `template`
--

INSERT INTO `template` (`id`, `nama`, `file`, `created_at`) VALUES
(2, 'Form Pendaftaran Persetujuan Tema Tugas Akhir', 'Persetujuan Tema dan Form Pendaftaran.docx', '2026-01-14 06:26:08'),
(3, 'Form Pendaftaran Seminar Proposal dan Berita Acara', 'Form Pendaftaran Seminar Proposal dan Berita Acara.docx', '2026-01-14 06:28:13'),
(4, 'Form Persetujuan Proposal Tugas Akhir', 'Lembar Persetujuan Proposal Tugas Akhir.docx', '2026-01-14 06:29:28'),
(5, 'Logbook Bimbingan', 'Logbook Bimbingan.docx', '2026-01-14 06:30:07'),
(6, 'Form Pendaftaran Ujian TA', 'Form Pendaftaran Ujian TA.docx', '2026-01-14 06:30:44'),
(7, 'Kehadiran Seminar Proposal', 'Kehadiran Seminar Proposal.docx', '2026-01-14 06:30:58'),
(8, 'Form Pendaftaran Ujian TA dan Undangan', 'Form Pendaftaran Ujian TA dan Undangan.docx', '2026-01-14 06:31:16'),
(9, 'Form Penilaian Ujian dan Tugas Akhir', 'Form Penilaian Ujian dan Tugas Akhir.docx', '2026-01-14 06:31:28'),
(10, 'Form Berita Acara Seminar Proposal', 'Berita Acara Seminar Proposal.docx', '2026-01-14 06:31:50'),
(11, 'Contoh Bukti Pembayaran', 'bukti pembayaran.pdf', '2026-01-14 06:32:08'),
(12, 'Contoh Bukti Kelulusan Mata Kuliah Magang atau Praktik Industri', 'khs.pdf', '2026-01-14 06:32:29'),
(13, 'Contoh Transkrip Nilai', 'transkip.pdf', '2026-01-14 06:32:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_nip` (`nip`);

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
-- Indexes for table `pengajuan_semhas`
--
ALTER TABLE `pengajuan_semhas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `pengajuan_ta_id` (`pengajuan_ta_id`);

--
-- Indexes for table `pengajuan_sempro`
--
ALTER TABLE `pengajuan_sempro`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengajuan_ta`
--
ALTER TABLE `pengajuan_ta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengajuan_mahasiswa` (`mahasiswa_id`);

--
-- Indexes for table `revisi_ta`
--
ALTER TABLE `revisi_ta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`);

--
-- Indexes for table `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dosbing_ta`
--
ALTER TABLE `dosbing_ta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pengajuan_semhas`
--
ALTER TABLE `pengajuan_semhas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengajuan_sempro`
--
ALTER TABLE `pengajuan_sempro`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengajuan_ta`
--
ALTER TABLE `pengajuan_ta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `revisi_ta`
--
ALTER TABLE `revisi_ta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `template`
--
ALTER TABLE `template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- Constraints for table `pengajuan_semhas`
--
ALTER TABLE `pengajuan_semhas`
  ADD CONSTRAINT `pengajuan_semhas_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`),
  ADD CONSTRAINT `pengajuan_semhas_ibfk_2` FOREIGN KEY (`pengajuan_ta_id`) REFERENCES `pengajuan_ta` (`id`);

--
-- Constraints for table `pengajuan_ta`
--
ALTER TABLE `pengajuan_ta`
  ADD CONSTRAINT `fk_pengajuan_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `revisi_ta`
--
ALTER TABLE `revisi_ta`
  ADD CONSTRAINT `revisi_ta_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_ta` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
