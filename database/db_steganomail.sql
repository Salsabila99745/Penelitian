/*
SQLyog Ultimate v11.11 (32 bit)
MySQL - 5.6.16 : Database - db_steganomail
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_steganomail` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `db_steganomail`;

/*Table structure for table `tbl_decrypt` */

DROP TABLE IF EXISTS `tbl_decrypt`;

CREATE TABLE `tbl_decrypt` (
  `id_decrypt` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `jenis_decrypt` varchar(20) DEFAULT NULL,
  `kode_encrypt` varchar(255) DEFAULT NULL,
  `plaintext` text,
  PRIMARY KEY (`id_decrypt`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `tbl_decrypt` */

/*Table structure for table `tbl_email` */

DROP TABLE IF EXISTS `tbl_email`;

CREATE TABLE `tbl_email` (
  `id_email` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `tbl_email` */

/*Table structure for table `tbl_encrypt` */

DROP TABLE IF EXISTS `tbl_encrypt`;

CREATE TABLE `tbl_encrypt` (
  `id_encrypt` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `kode_encrypt` varchar(255) DEFAULT NULL,
  `pesan` text,
  `ciphertext` text,
  `file` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_encrypt`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `tbl_encrypt` */

/*Table structure for table `tbl_level` */

DROP TABLE IF EXISTS `tbl_level`;

CREATE TABLE `tbl_level` (
  `id_level` int(11) NOT NULL AUTO_INCREMENT,
  `kode_level` varchar(10) DEFAULT NULL,
  `nama_level` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_level`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

/*Data for the table `tbl_level` */

insert  into `tbl_level`(`id_level`,`kode_level`,`nama_level`) values (1,'ADM','ADMIN'),(7,'USR','USER');

/*Table structure for table `tbl_user` */

DROP TABLE IF EXISTS `tbl_user`;

CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `id_level` int(11) DEFAULT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

/*Data for the table `tbl_user` */

insert  into `tbl_user`(`id_user`,`id_level`,`nama`,`no_hp`,`email`,`password`) values (1,1,'admin','08211','admin@gmail.com','202cb962ac59075b964b07152d234b70'),(60,7,'Mukti Diananingsih','0812','mukti@gmail.com','202cb962ac59075b964b07152d234b70'),(61,7,'Aji','082111','aji@gmail.com','202cb962ac59075b964b07152d234b70');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
