-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 27, 2016 at 11:43 PM
-- Server version: 5.5.49-0ubuntu0.14.04.1
-- PHP Version: 5.6.22-1+donate.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `Delivery`
--

-- --------------------------------------------------------

--
-- Table structure for table `Company`
--

CREATE TABLE IF NOT EXISTS `Company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `privilege` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Company`
--

INSERT INTO `Company` (`id`, `name`, `login`, `password`, `email`, `privilege`) VALUES
(1, 'test', 'test', '098f6bcd4621d373cade4e832627b4f6', 'test@localhost.com', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `Delivery_Points`
--

CREATE TABLE IF NOT EXISTS `Delivery_Points` (
  `Point_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Note` text COLLATE utf8_unicode_ci,
  `Total_Cost` double NOT NULL DEFAULT '0',
  `identifier_order` text COLLATE utf8_unicode_ci,
  `Street` text COLLATE utf8_unicode_ci NOT NULL,
  `House` text COLLATE utf8_unicode_ci NOT NULL,
  `Entry` text COLLATE utf8_unicode_ci,
  `floor` int(11) DEFAULT NULL,
  `flat` int(11) DEFAULT NULL,
  `Latitude` double NOT NULL,
  `Longitude` double NOT NULL,
  `phone_number` bigint(14) NOT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `Delivery_Date` date NOT NULL,
  `Order_Date` date NOT NULL,
  PRIMARY KEY (`Point_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `Orders`
--

CREATE TABLE IF NOT EXISTS `Orders` (
  `Order_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Point_ID` int(11) NOT NULL,
  `Description` text COLLATE utf8_unicode_ci NOT NULL,
  `Cost` double NOT NULL,
  PRIMARY KEY (`Order_ID`),
  KEY `Point_ID` (`Point_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Triggers `Orders`
--
DROP TRIGGER IF EXISTS `Add_Order`;
DELIMITER //
CREATE TRIGGER `Add_Order` AFTER INSERT ON `Orders`
 FOR EACH ROW BEGIN
   UPDATE Delivery_Points SET Total_Cost = (Total_Cost + New.Cost) WHERE Point_Id = New.Point_id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Delete_Order`;
DELIMITER //
CREATE TRIGGER `Delete_Order` AFTER DELETE ON `Orders`
 FOR EACH ROW BEGIN
	SET @total_cost := (Select Total_cost From Delivery_Points Where Point_Id = Old.Point_id);
	IF ((@total_cost- Old.Cost) >= 0) THEN
   		UPDATE Delivery_Points SET Total_Cost = (Total_Cost-Old.Cost) WHERE Point_Id = Old.Point_id;
	Else
		UPDATE Delivery_Points SET Total_Cost = 0 WHERE Point_Id = Old.Point_id;
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Update_Order`;
DELIMITER //
CREATE TRIGGER `Update_Order` AFTER UPDATE ON `Orders`
 FOR EACH ROW BEGIN
   UPDATE Delivery_Points SET Total_Cost = (Total_Cost- Old.Cost + New.Cost) WHERE Point_Id = New.Point_id;
END
//
DELIMITER ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `Orders_ibfk_1` FOREIGN KEY (`Point_ID`) REFERENCES `Delivery_Points` (`Point_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
