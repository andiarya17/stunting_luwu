-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Jul 2025 pada 13.46
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stunting_luwu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(10) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_log`
--

INSERT INTO `activity_log` (`id`, `activity`, `user_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(30, 'Cek stunting baru: amir - Normal', NULL, '::1', NULL, '2025-07-05 17:01:45'),
(31, 'Berita baru ditambahkan: Wabup Luwu Buka Rakor TPPS 2025, Tegaskan Percepatan Penurunan Stunting', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-10 03:54:46'),
(32, 'Berita diperbarui: Wabup Luwu Buka Rakor TPPS 2025, Tegaskan Percepatan Penurunan Stunting', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-10 03:55:55'),
(33, 'Berita diperbarui: Wabup Luwu Buka Rakor TPPS 2025, Tegaskan Percepatan Penurunan Stunting', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-10 03:59:34'),
(34, 'Cek stunting baru: lis - Severely Stunting', NULL, '::1', NULL, '2025-07-10 13:28:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `berita_terkini`
--

CREATE TABLE `berita_terkini` (
  `id` int(10) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `slug` varchar(255) DEFAULT NULL,
  `views` int(10) DEFAULT 0,
  `tanggal_buat` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `berita_terkini`
--

INSERT INTO `berita_terkini` (`id`, `judul`, `deskripsi`, `gambar`, `status`, `slug`, `views`, `tanggal_buat`, `updated_at`) VALUES
(8, 'Stunting di Mataram Turun Jadi 6,6 Persen, Pemkot Bidik di Bawah 5 Persen  Baca artikel detikbali, &quot;Stunting di Mataram Turun Jadi 6,6 Persen, Pemkot Bidik di Bawah 5 Persen', 'Mataram - Pemerintah Kota (Pemkot) Mataram, Nusa Tenggara Barat (NTB), terus menekan angka stunting di wilayahnya. Jika pada 2024 lalu angka stunting masih 7,6 persen atau sekitar 1.900 anak, kini turun menjadi 6,6 persen.\r\nWali Kota Mataram Mohan Roliskana mengatakan penurunan angka stunting ini cukup signifikan.\r\n\r\n&quot;Sampai dengan hari ini, kita di posisi 6,6 persen, alhamdulillah penurunannya sangat signifikan di tahun ini,&quot; kata Mohan saat diwawancarai di Mataram, Jumat (4/7/2025).', 'berita_1751705376_pxyyX.png', 'aktif', NULL, 0, '2025-07-05 08:49:36', '2025-07-05 08:49:36'),
(9, 'Wabup Luwu Buka Rakor TPPS 2025, Tegaskan Percepatan Penurunan Stunting', 'Wakil Bupati Luwu, Muh. Dhevy Bijak Pawindu, selaku Ketua Tim Percepatan Penurunan Stunting (TPPS) Kabupaten Luwu, secara resmi membuka Rapat Koordinasi TPPS Tahun 2025 yang digelar di Ruang Pola Andi Kambo, Kantor Bupati Luwu, Selasa (8/7/2025).\r\n\r\nDalam sambutannya, Dhevy menegaskan stunting bukan sekadar persoalan kesehatan, melainkan menyangkut masa depan kualitas sumber daya manusia dan produktivitas daerah.\r\n\r\n“Pemerintah Kabupaten Luwu melalui TPPS berkomitmen penuh untuk mengatasi persoalan ini secara terintegrasi dan berkelanjutan,” ujar Dhevy.\r\nIa menekankan bahwa upaya percepatan penurunan stunting membutuhkan kolaborasi lintas sektor.\r\n\r\nSeluruh pemangku kepentingan dari pemerintah desa, puskesmas, kader posyandu, tokoh masyarakat hingga pihak swasta harus terlibat secara aktif dan konsisten.\r\n\r\n“Dengan kerja keras, sinergi, dan inovasi, saya yakin kita dapat mencapai target penurunan stunting yang telah ditetapkan,” tambahnya.\r\nPlt. Kepala Dinas Pengendalian Penduduk dan Keluarga Berencana (DP2KB) Kabupaten Luwu, Masling, menjelaskan bahwa Rakor ini bertujuan meningkatkan komitmen serta koordinasi antaranggota TPPS untuk mengakselerasi penurunan angka stunting.\r\n\r\n“Selain itu, kegiatan ini juga bertujuan untuk menyinkronkan pelaksanaan program percepatan penurunan stunting antara pemerintah kabupaten, kecamatan, desa/kelurahan, serta seluruh pemangku kepentingan,” jelas Masling.\r\n\r\nSejumlah program unggulan TPPS Kabupaten Luwu pada tahun 2025 turut dipaparkan, antara lain Gerakan Orang Tua Asuh Cegah Stunting (GENTING), Mini Lokakarya, Rembug Stunting, Penyediaan Data Hasil Laporan Tim Pendamping Keluarga (TPK), serta Bapak/Bunda Asuh Anak Stunting (BAAS).', 'berita_1752119686_QyOGz.png', 'aktif', NULL, 0, '2025-07-10 03:54:46', '2025-07-10 03:59:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cek_stunting`
--

CREATE TABLE `cek_stunting` (
  `id` int(10) NOT NULL,
  `nama_anak` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `usia` int(10) NOT NULL COMMENT 'Usia dalam bulan',
  `berat_badan` decimal(5,2) NOT NULL COMMENT 'Berat badan dalam kg',
  `tinggi_badan` decimal(5,2) NOT NULL COMMENT 'Tinggi badan dalam cm',
  `hasil_cek` varchar(50) NOT NULL,
  `z_score` decimal(4,2) DEFAULT NULL,
  `tinggi_expected` decimal(5,2) DEFAULT NULL,
  `std_deviation` decimal(4,2) DEFAULT NULL,
  `nama_orangtua` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_cek` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cek_stunting`
--

INSERT INTO `cek_stunting` (`id`, `nama_anak`, `alamat`, `jenis_kelamin`, `usia`, `berat_badan`, `tinggi_badan`, `hasil_cek`, `z_score`, `tinggi_expected`, `std_deviation`, `nama_orangtua`, `no_telepon`, `catatan`, `tanggal_cek`) VALUES
(24, 'amir', 'larompong selatan', 'Laki-laki', 32, 30.00, 120.00, 'Normal', 8.00, 91.20, 3.60, NULL, NULL, NULL, '2025-07-05 17:01:45'),
(25, 'lis', 'wiwitan', 'Laki-laki', 23, 12.00, 67.00, 'Severely Stunting', -6.28, 87.10, 3.20, NULL, NULL, NULL, '2025-07-10 13:28:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `informasi_stunting`
--

CREATE TABLE `informasi_stunting` (
  `id` int(10) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `urutan` int(10) DEFAULT 0,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_diupdate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `informasi_stunting`
--

INSERT INTO `informasi_stunting` (`id`, `judul`, `konten`, `status`, `urutan`, `tanggal_dibuat`, `tanggal_diupdate`) VALUES
(1, 'Apa itu Stunting?', 'Stunting adalah kondisi gagal tumbuh pada anak balita (bawah lima tahun) akibat kekurangan gizi kronis terutama dalam 1.000 Hari Pertama Kehidupan (HPK), yaitu sejak janin dalam kandungan hingga anak berusia 2 tahun. Kondisi ini ditandai dengan tinggi badan anak yang lebih pendek dibandingkan dengan standar usianya menurut WHO. Stunting merupakan masalah gizi kronis yang dapat berdampak jangka panjang pada kualitas hidup anak.', 'aktif', 1, '2025-07-05 08:30:00', NULL),
(2, 'Gejala Stunting yang Mudah Dikenali', 'Gejala stunting yang dapat dikenali antara lain:\r\n1. Tinggi badan anak lebih pendek dari standar usianya menurut WHO (Z-score < -2 SD)\r\n2. Berat badan rendah untuk usianya\r\n3. Pertumbuhan tulang terlambat\r\n4. Performa buruk pada tes konsentrasi dan memori belajar\r\n5. Pubertas terlambat\r\n6. Usia 8-10 tahun anak menjadi lebih pendiam dan tidak banyak melakukan kontak mata\r\n7. Mudah sakit dan rentan terhadap infeksi\r\n8. Kemampuan kognitif menurun.', 'aktif', 2, '2025-07-05 08:30:00', '2025-07-11 11:24:58'),
(3, 'Penyebab Stunting pada Anak', 'Penyebab stunting meliputi:\n1. Kurangnya asupan gizi selama kehamilan dan masa pertumbuhan anak, terutama protein, zat besi, zinc, dan vitamin A\n2. Infeksi berulang atau kronis seperti diare, ISPA, dan cacingan\n3. Sanitasi dan kebersihan lingkungan yang buruk\n4. Pola asuh yang tidak tepat termasuk praktik pemberian makan\n5. Kemiskinan dan keterbatasan akses ke layanan kesehatan\n6. Pemberian ASI yang tidak optimal atau tidak eksklusif\n7. Pemberian makanan pendamping ASI (MPASI) yang tidak tepat waktu dan tidak bergizi\n8. Berat badan lahir rendah (BBLR)', 'aktif', 3, '2025-07-05 08:30:00', NULL),
(4, 'Cara Mencegah Stunting pada Anak', 'Cara mencegah stunting:\n1. Perbaikan gizi dan kesehatan ibu sejak remaja, hamil, dan menyusui dengan konsumsi makanan bergizi seimbang\n2. Pemberian ASI eksklusif selama 6 bulan pertama kehidupan\n3. Pemberian MPASI yang bergizi, aman, dan tepat waktu mulai usia 6 bulan dengan kandungan protein hewani\n4. Memantau pertumbuhan anak secara rutin di posyandu atau fasilitas kesehatan\n5. Menjaga kebersihan lingkungan dan sanitasi, termasuk akses air bersih dan jamban sehat\n6. Melakukan imunisasi lengkap sesuai jadwal\n7. Mengobati penyakit infeksi secara cepat dan tepat\n8. Menerapkan pola hidup bersih dan sehat (PHBS)', 'aktif', 4, '2025-07-05 08:30:00', NULL),
(5, 'Dampak Stunting Jangka Pendek dan Panjang', 'Dampak stunting jangka pendek:\n1. Meningkatnya angka kesakitan dan kematian\n2. Perkembangan kognitif, motorik, dan verbal anak terhambat\n3. Peningkatan biaya kesehatan\n\nDampak jangka panjang:\n1. Postur tubuh yang tidak optimal saat dewasa\n2. Meningkatnya risiko obesitas dan penyakit tidak menular seperti diabetes, hipertensi, dan penyakit jantung\n3. Menurunnya kapasitas belajar dan performa saat sekolah\n4. Menurunnya produktivitas dan kapasitas kerja sehingga mempengaruhi ekonomi\n5. Pada anak perempuan yang stunting, akan melahirkan bayi dengan berat badan lahir rendah\n6. Kemiskinan antar generasi sulit diputus', 'aktif', 5, '2025-07-05 08:30:00', NULL),
(6, 'Penanganan dan Intervensi Stunting', 'Penanganan stunting memerlukan pendekatan holistik:\r\n1. Intervensi gizi spesifik mencakup pemberian makanan tambahan, suplementasi gizi, dan promosi ASI eksklusif\r\n2. Intervensi gizi sensitif seperti peningkatan akses air bersih, sanitasi, pendidikan, dan pemberdayaan ekonomi\r\n3. Pemantauan pertumbuhan rutin dan deteksi dini melalui posyandu\r\n4. Edukasi dan konseling gizi untuk keluarga\r\n5. Penguatan sistem kesehatan dan kapasitas tenaga kesehatan\r\n6. Koordinasi lintas sektor antara kesehatan, pendidikan, sosial, dan ekonomi\r\n7. Pemberdayaan masyarakat dalam pencegahan dan penanganan stunting.', 'aktif', 6, '2025-07-05 08:30:00', '2025-07-11 11:24:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `login`
--

CREATE TABLE `login` (
  `id` int(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','superadmin') DEFAULT 'admin',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `login`
--

INSERT INTO `login` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin123', 'Administrator Sistem', 'admin@luwu.go.id', 'admin', 'aktif', '2025-07-05 08:30:00', '2025-07-04 14:11:39', '2025-07-05 08:30:00'),
(2, 'superadmin', 'super123', 'Super Administrator', 'superadmin@luwu.go.id', 'superadmin', 'aktif', NULL, '2025-07-04 14:11:39', '2025-07-04 14:11:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(10) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `key_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `key_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Sistem Monitoring Stunting Kabupaten Luwu', 'Nama website', '2025-07-05 08:25:00', '2025-07-05 08:25:00'),
(2, 'site_description', 'Platform monitoring dan edukasi stunting untuk meningkatkan kualitas tumbuh kembang anak di Kabupaten Luwu', 'Deskripsi website', '2025-07-05 08:25:00', '2025-07-05 08:25:00'),
(3, 'upload_max_size', '5242880', 'Maksimal ukuran upload file (bytes)', '2025-07-05 08:25:00', '2025-07-05 08:25:00'),
(4, 'allowed_image_types', 'jpg,jpeg,png,gif', 'Tipe file gambar yang diizinkan', '2025-07-05 08:25:00', '2025-07-05 08:25:00'),
(5, 'contact_email', 'info@luwu.go.id', 'Email kontak utama', '2025-07-05 08:25:00', '2025-07-05 08:25:00'),
(6, 'contact_phone', '0473-21234', 'Nomor telepon kontak', '2025-07-05 08:25:00', '2025-07-05 08:25:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indeks untuk tabel `berita_terkini`
--
ALTER TABLE `berita_terkini`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal` (`tanggal_buat`),
  ADD KEY `idx_views` (`views`);

--
-- Indeks untuk tabel `cek_stunting`
--
ALTER TABLE `cek_stunting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal` (`tanggal_cek`),
  ADD KEY `idx_hasil` (`hasil_cek`),
  ADD KEY `idx_jenis_kelamin` (`jenis_kelamin`),
  ADD KEY `idx_usia` (`usia`),
  ADD KEY `idx_nama_anak` (`nama_anak`);

--
-- Indeks untuk tabel `informasi_stunting`
--
ALTER TABLE `informasi_stunting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_urutan` (`urutan`),
  ADD KEY `idx_tanggal_dibuat` (`tanggal_dibuat`);

--
-- Indeks untuk tabel `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_key_name` (`key_name`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `berita_terkini`
--
ALTER TABLE `berita_terkini`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `cek_stunting`
--
ALTER TABLE `cek_stunting`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `informasi_stunting`
--
ALTER TABLE `informasi_stunting`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `login`
--
ALTER TABLE `login`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
