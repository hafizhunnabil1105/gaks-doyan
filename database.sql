-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Nov 2026 pada 02.39
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
-- Database: `gaks_doyan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`) VALUES
(1, 'Rice Bowl'),
(2, 'Minuman');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(150) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Bahan baku',
  `jumlah` int(11) NOT NULL DEFAULT 0,
  `dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `tanggal`, `keterangan`, `kategori`, `jumlah`, `dibuat`) VALUES
(1, '2026-11-17', 'Belanja ayam dan sapi', 'Bahan baku', 150000, '2026-11-17 11:09:44'),
(2, '2026-11-17', 'Gas dan packaging', 'Operasional', 60000, '2026-11-17 11:09:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `metode_ambil` varchar(20) NOT NULL DEFAULT 'Antar',
  `metode_bayar` varchar(30) NOT NULL DEFAULT 'Transfer Bank',
  `subtotal` int(11) NOT NULL DEFAULT 0,
  `ongkir` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Baru',
  `status_bayar` varchar(30) NOT NULL DEFAULT 'Menunggu verifikasi',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `kode`, `user_id`, `nama_pelanggan`, `no_hp`, `alamat`, `metode_ambil`, `metode_bayar`, `subtotal`, `ongkir`, `total`, `status`, `status_bayar`, `catatan`, `created_at`) VALUES
(1, 'GD2611179C98', 2, 'budi', '089876452653', 'jalan', 'Antar', 'COD', 0, 0, 10000, 'Selesai', 'Lunas', '', '2026-11-17 11:36:54'),
(2, 'GD2611187492', 2, 'budi', '081234567890', 'jalan', 'Antar', 'QRIS', 0, 0, 10000, 'Diantar', 'Lunas', '', '2026-11-17 22:34:29'),
(3, 'GD2611181D0C', 2, 'budi', '089876452653', 'jalan', 'Antar', 'Transfer', 0, 0, 12000, 'Selesai', 'Lunas', '', '2026-11-17 22:35:10'),
(4, 'GD261118693E', 2, 'budi', '089876452653', 'jalan', 'Antar', 'COD', 0, 0, 12000, 'Baru', 'Menunggu verifikasi', '', '2026-11-17 22:55:12'),
(5, 'GD261118ED49', 2, 'budi', '081234567890', 'jalan cilangkap', 'Antar', 'COD', 0, 0, 20000, 'Baru', 'Menunggu verifikasi', '', '2026-11-18 13:50:51'),
(6, 'GD261118E18E', 2, 'budi', '081234567890', 'jalan cilangkap', 'Antar', 'COD', 0, 0, 20000, 'Diproses', 'Lunas', '', '2026-11-18 13:52:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan_detail`
--

CREATE TABLE `pesanan_detail` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `nama_produk` varchar(120) DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) NOT NULL DEFAULT 1,
  `subtotal` int(11) NOT NULL DEFAULT 0,
  `keterangan` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan_detail`
--

INSERT INTO `pesanan_detail` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `harga`, `qty`, `subtotal`, `keterangan`) VALUES
(1, 1, 3, 'Es Kopi Dalgona (Hot)', 10000, 1, 10000, ''),
(2, 2, 3, 'Es Kopi Dalgona (Hot)', 10000, 1, 10000, ''),
(3, 3, 2, 'Es Soda Gembira', 12000, 1, 12000, ''),
(4, 4, 2, 'Es Soda Gembira', 12000, 1, 12000, ''),
(5, 5, 1, 'Rice Bowl Saus Mentai', 20000, 1, 20000, 'Topping: Telur Mata Sapi'),
(6, 6, 1, 'Rice Bowl Saus Mentai', 20000, 1, 20000, 'Topping: Telur Mata Sapi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int(11) NOT NULL,
  `nama` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Rice Bowl',
  `kategori_id` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 0,
  `stok` int(11) NOT NULL DEFAULT 50,
  `gambar` varchar(200) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tersedia` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `nama`, `kategori`, `kategori_id`, `deskripsi`, `harga`, `stok`, `gambar`, `aktif`, `created_at`, `tersedia`) VALUES
(1, 'Rice Bowl Saus Mentai', 'Rice Bowl', 1, 'Ayam crispy dengan saus mentai gurih.', 17000, 48, 'produk_6ac3515db125d.jpeg', 1, '2026-11-17 11:22:27', 1),
(2, 'Es Soda Gembira', 'Minuman', 2, 'Soda segar dengan sirup dan susu, topping es krim.', 12000, 98, 'produk_6acf93636d99b.jpeg', 1, '2026-11-17 11:22:27', 1),
(3, 'Es Kopi Dalgona', 'Minuman', 2, 'Kopi dalgona creamy, pilihan reguler dan jumbo.', 12000, 98, 'produk_6acf9374672a2.jpeg', 1, '2026-11-17 11:22:27', 1),
(4, 'Ice Lemon Tea', 'Minuman', 2, 'Teh lemon dingin menyegarkan, pilihan reguler dan jumbo.', 8000, 100, 'produk_6acf938503d32.jpeg', 1, '2026-11-17 11:22:27', 1),
(5, 'Es Teh', 'Minuman', 2, 'Es teh manis, pilihan reguler dan jumbo.', 6000, 100, 'produk_6acf939383eb4.jpeg', 1, '2026-11-17 11:22:27', 1),
(6, 'Rice Bowl Mozzarella', 'Rice Bowl', 1, 'Topping mozzarella lumer, dibakar sebentar.', 18000, 50, 'produk_6acf93ab6c751.jpeg', 1, '2026-11-17 11:22:27', 1),
(7, 'Rice Bowl Sapi Teriyaki', 'Rice Bowl', 1, 'Nasi hangat dengan sapi teriyaki empuk.', 17000, 50, 'produk_6acf93c6ac240.jpeg', 1, '2026-11-17 11:22:27', 1),
(8, 'Rice Bowl Ayam Teriyaki', 'Rice Bowl', 1, 'Nasi hangat, ayam teriyaki juicy, sayur segar.', 15000, 50, 'produk_6acf93dc175a3.jpeg', 1, '2026-11-17 11:22:27', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_topping`
--

CREATE TABLE `produk_topping` (
  `produk_id` int(11) NOT NULL,
  `topping_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_topping`
--

INSERT INTO `produk_topping` (`produk_id`, `topping_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(8, 1),
(8, 2),
(8, 3),
(8, 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_varian`
--

CREATE TABLE `produk_varian` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama` varchar(60) NOT NULL,
  `harga` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_varian`
--

INSERT INTO `produk_varian` (`id`, `produk_id`, `nama`, `harga`) VALUES
(1, 3, 'Hot', 10000),
(2, 3, 'Es', 12000),
(3, 4, 'Reguler', 6000),
(4, 4, 'Jumbo', 8000),
(5, 5, 'Reguler', 4000),
(6, 5, 'Jumbo', 6000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo`
--

CREATE TABLE `promo` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `gambar` varchar(200) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promo`
--

INSERT INTO `promo` (`id`, `judul`, `deskripsi`, `tag`, `gambar`, `aktif`, `created_at`) VALUES
(1, '1 Rice Bowl Ayam Teriyaki', '+ 1 Rice Bowl Sapi Teriyaki', 'HEMAT 20%', NULL, 1, '2026-11-17 11:09:44'),
(2, '1 Rice Bowl Mozzarella', '+ Ice Lemon Tea Jumbo', 'PAKET HEMAT', NULL, 1, '2026-11-17 11:09:44'),
(3, 'Rice Bowl Cabe Ijo / Cabe Matah', '+ GRATIS AIR MINERAL', 'SETIAP HARI JUMAT', NULL, 1, '2026-11-17 11:09:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `testimoni`
--

CREATE TABLE `testimoni` (
  `id` int(11) NOT NULL,
  `nama` varchar(80) NOT NULL,
  `isi` varchar(300) NOT NULL,
  `bintang` tinyint(4) NOT NULL DEFAULT 5,
  `tampil` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `testimoni`
--

INSERT INTO `testimoni` (`id`, `nama`, `isi`, `bintang`, `tampil`, `dibuat`) VALUES
(1, 'Rina', 'Ayam teriyakinya juicy banget, porsinya pas buat makan siang. Pesan lewat web gampang!', 5, 1, '2026-11-17 11:09:44'),
(2, 'Dimas', 'Saus mentainya juara. Antarnya cepat, masih hangat pas sampai.', 5, 1, '2026-11-17 11:09:44'),
(3, 'Salsa', 'Harga ramah kantong mahasiswa dan rasanya konsisten. Pasti repeat order.', 4, 1, '2026-11-17 11:09:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `topping`
--

CREATE TABLE `topping` (
  `id` int(11) NOT NULL,
  `nama` varchar(60) NOT NULL,
  `harga` int(11) NOT NULL DEFAULT 0,
  `tersedia` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `topping`
--

INSERT INTO `topping` (`id`, `nama`, `harga`, `tersedia`) VALUES
(1, 'Telur Mata Sapi', 3000, 1),
(2, 'Extra Keju', 4000, 1),
(3, 'Extra Nasi', 3000, 1),
(4, 'Sambal', 1000, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'pelanggan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `email`, `password`, `telepon`, `alamat`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin', 'admin@gaksdoyan.com', '$2y$10$Wutap1Dq2nVYV8apKLVkf.Iy0AqMQys738ICWjY1b3G5SEOfqPvB.', '081234567890', 'Jl. Contoh No. 1', 'admin', '2026-11-17 11:09:43'),
(2, 'budi', 'budi', 'budi@gmail.com', '$2y$10$R9msLyAYJGwBIBAbsZM6gOo.S6UzeqfHxy51E2d2QLk.1ANn7fzpO', '081300000001', 'Jl. Melati No. 5', 'pelanggan', '2026-11-17 11:09:43');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indeks untuk tabel `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `produk_topping`
--
ALTER TABLE `produk_topping`
  ADD PRIMARY KEY (`produk_id`,`topping_id`);

--
-- Indeks untuk tabel `produk_varian`
--
ALTER TABLE `produk_varian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `topping`
--
ALTER TABLE `topping`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `produk_varian`
--
ALTER TABLE `produk_varian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `promo`
--
ALTER TABLE `promo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `topping`
--
ALTER TABLE `topping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
