-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 10, 2016 at 06:13 PM
-- Server version: 5.5.47-0ubuntu0.14.04.1
-- PHP Version: 5.6.19-1+deb.sury.org~trusty+1

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Dumping data for table `Delivery_Points`
--

INSERT INTO `Delivery_Points` (`Point_ID`, `Note`, `Total_Cost`, `identifier_order`, `Street`, `House`, `Entry`, `floor`, `flat`, `Latitude`, `Longitude`, `phone_number`, `time_start`, `time_end`, `Delivery_Date`, `Order_Date`) VALUES
(1, 'TODO', 0, '20160410#1', 'улица Рафиева', '113', NULL, NULL, NULL, 53.856689, 27.43383, 0, '18:00:00', '18:45:00', '2016-04-10', '2016-04-10'),
(2, 'TODO', 0, '20160410#2', 'проспект Независимости', '102', NULL, NULL, NULL, 53.928424, 27.630651, 0, '20:00:00', '22:00:00', '2016-04-10', '2016-04-10'),
(3, 'TODO', 0, '20160410#3', 'улица Притыцкого', '100', NULL, NULL, NULL, 53.907976, 27.443092, 0, '19:00:00', '20:00:00', '2016-04-10', '2016-04-10'),
(4, 'TODO', 0, '20160410#4', 'улица Веры Хоружей', '1', NULL, NULL, NULL, 53.916143, 27.580911, 0, '17:00:00', '22:00:00', '2016-04-10', '2016-04-10'),
(5, 'TODO', 0, '20160410#5', 'Садовая улица', '18', NULL, NULL, NULL, 53.941103, 27.714096, 0, '18:00:00', '19:30:00', '2016-04-10', '2016-04-10'),
(6, 'TODO', 0, '20160410#6', 'улица Чижевских', '2', NULL, NULL, NULL, 53.849843, 27.572692, 0, '17:00:00', '22:00:00', '2016-04-10', '2016-04-10'),
(7, 'TODO', 0, '20160410#7', 'Ташкентская улица', '1', NULL, NULL, NULL, 53.83492, 27.630013, 0, '17:00:00', '22:00:00', '2016-04-10', '2016-04-10'),
(8, 'TODO', 0, '20160410#8', 'Столичная улица', '17', NULL, NULL, NULL, 53.950276, 27.512055, 0, '17:00:00', '22:00:00', '2016-04-10', '2016-04-10');

--
-- Triggers `Delivery_Points`
--
DROP TRIGGER IF EXISTS `Delete Route After Add point`;
DELIMITER //
CREATE TRIGGER `Delete Route After Add point` AFTER INSERT ON `Delivery_Points`
 FOR EACH ROW BEGIN
	DELETE FROM Routes WHERE calculating_date=new.Delivery_Date;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Delete Route After Delete point`;
DELIMITER //
CREATE TRIGGER `Delete Route After Delete point` AFTER DELETE ON `Delivery_Points`
 FOR EACH ROW BEGIN
	DELETE FROM Routes WHERE calculating_date=OLD.Delivery_Date;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Delete Route After Update point`;
DELIMITER //
CREATE TRIGGER `Delete Route After Update point` AFTER UPDATE ON `Delivery_Points`
 FOR EACH ROW BEGIN
	IF (NEW.STREET != OLD.STREET OR NEW.HOUSE != OLD.HOUSE OR OLD.Delivery_Date != NEW.Delivery_Date OR OLD.time_start != NEW.time_start OR OLD.time_end != NEW.time_end) THEN
		DELETE FROM Routes WHERE (calculating_date=OLD.Delivery_Date OR calculating_date=new.Delivery_Date);
	END IF;
END
//
DELIMITER ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=46 ;

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

-- --------------------------------------------------------

--
-- Table structure for table `Routes`
--

CREATE TABLE IF NOT EXISTS `Routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calculating_date` date NOT NULL,
  `routes` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=22 ;

--
-- Dumping data for table `Routes`
--

INSERT INTO `Routes` (`id`, `calculating_date`, `routes`) VALUES
(21, '2016-03-31', 'YToyOntpOjA7YToyOntzOjY6InBvaW50cyI7YTo3OntpOjA7YTo1OntzOjg6InBvaW50X2lkIjtpOjQ7czo4OiJsYXRpdHVkZSI7ZDo1My45MTYxNDI5OTk5OTk5OTg7czo5OiJsb25naXR1ZGUiO2Q6MjcuNTgwOTExO3M6NzoiYWRkcmVzcyI7czozNjoi0YPQu9C40YbQsCDQktC10YDRiyDQpdC+0YDRg9C20LXQuSAxIjtzOjQ6InRpbWUiO2Q6NjUzODAuMjY5OTk5OTk5OTk3O31pOjE7YTo1OntzOjg6InBvaW50X2lkIjtpOjg7czo4OiJsYXRpdHVkZSI7ZDo1My45NTAyNzYwMDAwMDAwMDI7czo5OiJsb25naXR1ZGUiO2Q6MjcuNTEyMDU1O3M6NzoiYWRkcmVzcyI7czozMjoi0KHRgtC+0LvQuNGH0L3QsNGPINGD0LvQuNGG0LAgMTciO3M6NDoidGltZSI7ZDo2NjkzOS4yMzk5OTk5OTk5OTE7fWk6MjthOjU6e3M6ODoicG9pbnRfaWQiO2k6NTtzOjg6ImxhdGl0dWRlIjtkOjUzLjk0MTEwMjk5OTk5OTk5ODtzOjk6ImxvbmdpdHVkZSI7ZDoyNy43MTQwOTYwMDAwMDAwMDE7czo3OiJhZGRyZXNzIjtzOjI4OiLQodCw0LTQvtCy0LDRjyDRg9C70LjRhtCwIDE4IjtzOjQ6InRpbWUiO2Q6NjkxODMuMzE5OTk5OTk5OTkyO31pOjM7YTo1OntzOjg6InBvaW50X2lkIjtpOjM7czo4OiJsYXRpdHVkZSI7ZDo1My45MDc5NzU5OTk5OTk5OTg7czo5OiJsb25naXR1ZGUiO2Q6MjcuNDQzMDkyO3M6NzoiYWRkcmVzcyI7czozNToi0YPQu9C40YbQsCDQn9GA0LjRgtGL0YbQutC+0LPQviAxMDAiO3M6NDoidGltZSI7ZDo3MTgwMS45Mzk5OTk5OTk5ODg7fWk6NDthOjU6e3M6ODoicG9pbnRfaWQiO2k6NjtzOjg6ImxhdGl0dWRlIjtkOjUzLjg0OTg0MztzOjk6ImxvbmdpdHVkZSI7ZDoyNy41NzI2OTI7czo3OiJhZGRyZXNzIjtzOjMxOiLRg9C70LjRhtCwINCn0LjQttC10LLRgdC60LjRhSAyIjtzOjQ6InRpbWUiO2Q6NzM3MDcuNTI5OTk5OTk5OTg0O31pOjU7YTo1OntzOjg6InBvaW50X2lkIjtpOjc7czo4OiJsYXRpdHVkZSI7ZDo1My44MzQ5MTk5OTk5OTk5OTc7czo5OiJsb25naXR1ZGUiO2Q6MjcuNjMwMDEzMDAwMDAwMDAyO3M6NzoiYWRkcmVzcyI7czozNToi0KLQsNGI0LrQtdC90YLRgdC60LDRjyDRg9C70LjRhtCwIDEiO3M6NDoidGltZSI7ZDo3NTA0MC4wNDk5OTk5OTk5ODg7fWk6NjthOjU6e3M6ODoicG9pbnRfaWQiO2k6MjtzOjg6ImxhdGl0dWRlIjtkOjUzLjkyODQyNDtzOjk6ImxvbmdpdHVkZSI7ZDoyNy42MzA2NTE7czo3OiJhZGRyZXNzIjtzOjQ3OiLQv9GA0L7RgdC/0LXQutGCINCd0LXQt9Cw0LLQuNGB0LjQvNC+0YHRgtC4IDEwMiI7czo0OiJ0aW1lIjtkOjc3MDYzLjMxOTk5OTk5OTk5Mjt9fXM6MTA6InRvdGFsX3RpbWUiO2Q6Nzc2NjMuMzE5OTk5OTk5OTkyO31pOjE7YToyOntzOjY6InBvaW50cyI7YToxOntpOjA7YTo1OntzOjg6InBvaW50X2lkIjtpOjE7czo4OiJsYXRpdHVkZSI7ZDo1My44NTY2ODkwMDAwMDAwMDM7czo5OiJsb25naXR1ZGUiO2Q6MjcuNDMzODM7czo3OiJhZGRyZXNzIjtzOjI5OiLRg9C70LjRhtCwINCg0LDRhNC40LXQstCwIDExMyI7czo0OiJ0aW1lIjtkOjY2NDU2LjExOTk5OTk5OTk5NTt9fXM6MTA6InRvdGFsX3RpbWUiO2Q6NjcwNTYuMTE5OTk5OTk5OTk1O319');

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
