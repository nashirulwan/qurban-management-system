/*M!999999\- enable the sandbox mode */

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `distribusi_daging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `distribusi_daging` (
  `id_distribusi` int(11) NOT NULL AUTO_INCREMENT,
  `nik_penerima` varchar(20) NOT NULL,
  `id_pembagian` int(11) NOT NULL,
  `id_periode` int(11) NOT NULL,
  `nomor_paket` varchar(20) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `berat_daging` decimal(5,2) NOT NULL,
  `status_ambil` enum('belum_ambil','sudah_ambil') DEFAULT 'belum_ambil',
  `tanggal_ambil` datetime DEFAULT NULL,
  `nik_penyerah` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_distribusi`),
  UNIQUE KEY `nomor_paket` (`nomor_paket`),
  UNIQUE KEY `qr_code` (`qr_code`),
  KEY `nik_penerima` (`nik_penerima`),
  KEY `id_pembagian` (`id_pembagian`),
  KEY `id_periode` (`id_periode`),
  KEY `nik_penyerah` (`nik_penyerah`),
  CONSTRAINT `fk_distribusi_pembagian` FOREIGN KEY (`id_pembagian`) REFERENCES `pembagian_daging` (`id_pembagian`),
  CONSTRAINT `fk_distribusi_penerima` FOREIGN KEY (`nik_penerima`) REFERENCES `users` (`nik`),
  CONSTRAINT `fk_distribusi_penyerah` FOREIGN KEY (`nik_penyerah`) REFERENCES `users` (`nik`),
  CONSTRAINT `fk_distribusi_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hewan_qurban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hewan_qurban` (
  `id_hewan` int(11) NOT NULL AUTO_INCREMENT,
  `id_periode` int(11) NOT NULL,
  `jenis_hewan` enum('sapi','kambing','domba') NOT NULL,
  `nomor_hewan` varchar(10) NOT NULL,
  `harga_hewan` decimal(12,2) NOT NULL,
  `biaya_admin` decimal(10,2) NOT NULL,
  `estimasi_daging` decimal(5,2) NOT NULL,
  `status` enum('rencana','tersedia','terpesan','disembelih','dibagikan') DEFAULT 'rencana',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_hewan`),
  KEY `id_periode` (`id_periode`),
  CONSTRAINT `fk_hewan_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `panitia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `panitia` (
  `id_panitia` int(11) NOT NULL AUTO_INCREMENT,
  `nik_panitia` varchar(20) NOT NULL,
  `id_periode` int(11) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_panitia`),
  UNIQUE KEY `unique_panitia` (`nik_panitia`,`id_periode`),
  KEY `id_periode` (`id_periode`),
  CONSTRAINT `fk_panitia_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`),
  CONSTRAINT `fk_panitia_user` FOREIGN KEY (`nik_panitia`) REFERENCES `users` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembagian_daging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembagian_daging` (
  `id_pembagian` int(11) NOT NULL AUTO_INCREMENT,
  `id_periode` int(11) NOT NULL,
  `id_hewan` int(11) NOT NULL,
  `kategori_penerima` enum('warga','berqurban','panitia') NOT NULL,
  `total_berat` decimal(6,2) NOT NULL,
  `jumlah_paket` int(11) NOT NULL,
  `berat_per_paket` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pembagian`),
  KEY `id_periode` (`id_periode`),
  KEY `id_hewan` (`id_hewan`),
  CONSTRAINT `fk_pembagian_hewan` FOREIGN KEY (`id_hewan`) REFERENCES `hewan_qurban` (`id_hewan`),
  CONSTRAINT `fk_pembagian_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran_iuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_iuran` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `nik_pembayar` varchar(20) NOT NULL,
  `id_periode` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `jenis_iuran` enum('qurban_sapi','qurban_kambing','administrasi_sapi','administrasi_kambing','lainnya') NOT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `tanggal_bayar` datetime NOT NULL,
  `metode_bayar` enum('tunai','transfer') DEFAULT 'tunai',
  `status_verifikasi` enum('pending','terverifikasi','ditolak') DEFAULT 'pending',
  `nik_verifikator` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pembayaran`),
  KEY `nik_pembayar` (`nik_pembayar`),
  KEY `id_periode` (`id_periode`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `nik_verifikator` (`nik_verifikator`),
  CONSTRAINT `fk_pembayaran_pembayar` FOREIGN KEY (`nik_pembayar`) REFERENCES `users` (`nik`),
  CONSTRAINT `fk_pembayaran_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`),
  CONSTRAINT `fk_pembayaran_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_keuangan` (`id_transaksi`) ON DELETE SET NULL,
  CONSTRAINT `fk_pembayaran_verifikator` FOREIGN KEY (`nik_verifikator`) REFERENCES `users` (`nik`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `periode_qurban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `periode_qurban` (
  `id_periode` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_hijriah` varchar(10) NOT NULL,
  `tahun_masehi` year(4) NOT NULL,
  `tanggal_pelaksanaan` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_periode`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `peserta_qurban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `peserta_qurban` (
  `id_peserta` int(11) NOT NULL AUTO_INCREMENT,
  `nik_peserta` varchar(20) NOT NULL,
  `id_hewan` int(11) NOT NULL,
  `id_periode` int(11) NOT NULL,
  `bagian_hewan` decimal(3,2) DEFAULT 1.00,
  `nominal_bayar` decimal(10,2) NOT NULL,
  `status_bayar` enum('belum_bayar','dp','lunas') DEFAULT 'belum_bayar',
  `tanggal_daftar` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_peserta`),
  KEY `nik_peserta` (`nik_peserta`),
  KEY `id_hewan` (`id_hewan`),
  KEY `id_periode` (`id_periode`),
  CONSTRAINT `fk_peserta_hewan` FOREIGN KEY (`id_hewan`) REFERENCES `hewan_qurban` (`id_hewan`),
  CONSTRAINT `fk_peserta_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`),
  CONSTRAINT `fk_peserta_user` FOREIGN KEY (`nik_peserta`) REFERENCES `users` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaksi_keuangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_keuangan` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `id_periode` int(11) NOT NULL,
  `jenis_transaksi` enum('masuk','keluar') NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `nik_user_input` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_transaksi`),
  KEY `id_periode` (`id_periode`),
  KEY `nik_user_input` (`nik_user_input`),
  CONSTRAINT `fk_transaksi_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_qurban` (`id_periode`),
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`nik_user_input`) REFERENCES `users` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `nik` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_kk` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_panitia` tinyint(1) NOT NULL DEFAULT 0,
  `is_warga` tinyint(1) NOT NULL DEFAULT 0,
  `is_berqurban` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`nik`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v_rekapitulasi_keuangan`;
/*!50001 DROP VIEW IF EXISTS `v_rekapitulasi_keuangan`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_rekapitulasi_keuangan` AS SELECT
 NULL AS `tahun_masehi`,
 NULL AS `tahun_hijriah`,
 NULL AS `total_masuk`,
 NULL AS `total_keluar`,
 NULL AS `saldo` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_status_distribusi`;
/*!50001 DROP VIEW IF EXISTS `v_status_distribusi`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_status_distribusi` AS SELECT
 NULL AS `tahun_masehi`,
 NULL AS `belum_ambil`,
 NULL AS `sudah_ambil`,
 NULL AS `total_paket` */;
SET character_set_client = @saved_cs_client;
/*!50001 DROP VIEW IF EXISTS `v_rekapitulasi_keuangan`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_rekapitulasi_keuangan` AS select `p`.`tahun_masehi` AS `tahun_masehi`,`p`.`tahun_hijriah` AS `tahun_hijriah`,sum(case when `t`.`jenis_transaksi` = 'masuk' then `t`.`nominal` else 0 end) AS `total_masuk`,sum(case when `t`.`jenis_transaksi` = 'keluar' then `t`.`nominal` else 0 end) AS `total_keluar`,sum(case when `t`.`jenis_transaksi` = 'masuk' then `t`.`nominal` else -`t`.`nominal` end) AS `saldo` from (`periode_qurban` `p` left join `transaksi_keuangan` `t` on(`p`.`id_periode` = `t`.`id_periode`)) group by `p`.`id_periode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_status_distribusi`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_status_distribusi` AS select `p`.`tahun_masehi` AS `tahun_masehi`,count(case when `d`.`status_ambil` = 'belum_ambil' then 1 end) AS `belum_ambil`,count(case when `d`.`status_ambil` = 'sudah_ambil' then 1 end) AS `sudah_ambil`,count(0) AS `total_paket` from (`distribusi_daging` `d` join `periode_qurban` `p` on(`d`.`id_periode` = `p`.`id_periode`)) group by `p`.`id_periode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;



-- Anonymous local demo data. Change or remove these credentials before deployment.
START TRANSACTION;
INSERT INTO `periode_qurban` (`id_periode`, `tahun_hijriah`, `tahun_masehi`, `tanggal_pelaksanaan`, `is_active`)
VALUES (1, '1446', 2025, '2025-06-06', 1);

INSERT INTO `users` (`nik`, `username`, `password`, `nama_lengkap`, `is_active`, `is_admin`, `is_panitia`, `is_warga`, `is_berqurban`) VALUES
('9000000000000001', 'demo_admin', '$2y$10$n18jxyDH0Ut3DWRMXG.VP.5aFn7vsC0RyfBjtNFRJAuHMnN3AJNzK', 'Demo Administrator', 1, 1, 1, 1, 1),
('9000000000000002', 'demo_warga', '$2y$10$CgDs5TjvLEWAYJ8XvHxQMuBWIj6FtzFcOVBjgtZFLuYNfRInqUxrK', 'Demo Warga', 1, 0, 0, 1, 0);

INSERT INTO `hewan_qurban` (`id_hewan`, `id_periode`, `jenis_hewan`, `nomor_hewan`, `harga_hewan`, `biaya_admin`, `estimasi_daging`, `status`) VALUES
(1, 1, 'sapi', 'S-01', 21000000.00, 100000.00, 80.00, 'tersedia'),
(2, 1, 'kambing', 'K-01', 2700000.00, 50000.00, 20.00, 'tersedia');
COMMIT;
