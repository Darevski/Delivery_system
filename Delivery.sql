-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 10, 2016 at 03:50 PM
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
	IF (NEW.STREET != OLD.STREET OR NEW.HOUSE != OLD.HOUSE OR OLD.Delivery_Date != NEW.Delivery_Date) THEN
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

--
-- Dumping data for table `Routes`
--

INSERT INTO `Routes` (`id`, `calculating_date`, `routes`) VALUES
(16, '2016-04-10', 'YTo0OntpOjA7YToyOntzOjY6InBvaW50cyI7YTo0OntpOjA7YTo2OntzOjg6ImxhdGl0dWRlIjtkOjUzLjkxNjE0Mjk5OTk5OTk5ODtzOjk6ImxvbmdpdHVkZSI7ZDoyNy41ODA5MTE7czoxMDoidGltZV9zdGFydCI7czo4OiIxNzowMDowMCI7czo4OiJ0aW1lX2VuZCI7czo4OiIyMjowMDowMCI7czo3OiJhZGRyZXNzIjtzOjM2OiLRg9C70LjRhtCwINCS0LXRgNGLINCl0L7RgNGD0LbQtdC5IDEiO3M6NDoidGltZSI7ZDo2NTM4MC4yNjk5OTk5OTk5OTc7fWk6MTthOjY6e3M6ODoibGF0aXR1ZGUiO2Q6NTMuOTI4NDI0O3M6OToibG9uZ2l0dWRlIjtkOjI3LjYzMDY1MTtzOjEwOiJ0aW1lX3N0YXJ0IjtzOjg6IjIwOjAwOjAwIjtzOjg6InRpbWVfZW5kIjtzOjg6IjIyOjAwOjAwIjtzOjc6ImFkZHJlc3MiO3M6NDc6ItC/0YDQvtGB0L/QtdC60YIg0J3QtdC30LDQstC40YHQuNC80L7RgdGC0LggMTAyIjtzOjQ6InRpbWUiO2Q6NzI1MzkuNjk5OTk5OTk5OTk3O31pOjI7YTo2OntzOjg6ImxhdGl0dWRlIjtkOjUzLjgzNDkxOTk5OTk5OTk5NztzOjk6ImxvbmdpdHVkZSI7ZDoyNy42MzAwMTMwMDAwMDAwMDI7czoxMDoidGltZV9zdGFydCI7czo4OiIxNzowMDowMCI7czo4OiJ0aW1lX2VuZCI7czo4OiIyMjowMDowMCI7czo3OiJhZGRyZXNzIjtzOjM1OiLQotCw0YjQutC10L3RgtGB0LrQsNGPINGD0LvQuNGG0LAgMSI7czo0OiJ0aW1lIjtkOjc0MzA2LjU5OTk5OTk5OTk5MTt9aTozO2E6Njp7czo4OiJsYXRpdHVkZSI7ZDo1My44NDk4NDM7czo5OiJsb25naXR1ZGUiO2Q6MjcuNTcyNjkyO3M6MTA6InRpbWVfc3RhcnQiO3M6ODoiMTc6MDA6MDAiO3M6ODoidGltZV9lbmQiO3M6ODoiMjI6MDA6MDAiO3M6NzoiYWRkcmVzcyI7czozMToi0YPQu9C40YbQsCDQp9C40LbQtdCy0YHQutC40YUgMiI7czo0OiJ0aW1lIjtkOjc1MzQzLjk4OTk5OTk5OTk5MTt9fXM6MTA6InRvdGFsX3RpbWUiO2Q6NzU5NDMuOTg5OTk5OTk5OTkxO31pOjE7YToyOntzOjY6InBvaW50cyI7YToyOntpOjA7YTo2OntzOjg6ImxhdGl0dWRlIjtkOjUzLjk1MDI3NjAwMDAwMDAwMjtzOjk6ImxvbmdpdHVkZSI7ZDoyNy41MTIwNTU7czoxMDoidGltZV9zdGFydCI7czo4OiIxNzowMDowMCI7czo4OiJ0aW1lX2VuZCI7czo4OiIyMjowMDowMCI7czo3OiJhZGRyZXNzIjtzOjMyOiLQodGC0L7Qu9C40YfQvdCw0Y8g0YPQu9C40YbQsCAxNyI7czo0OiJ0aW1lIjtkOjY1NzY3LjYzOTk5OTk5OTk5OTt9aToxO2E6Njp7czo4OiJsYXRpdHVkZSI7ZDo1My45MDc5NzU5OTk5OTk5OTg7czo5OiJsb25naXR1ZGUiO2Q6MjcuNDQzMDkyO3M6MTA6InRpbWVfc3RhcnQiO3M6ODoiMTk6MDA6MDAiO3M6ODoidGltZV9lbmQiO3M6ODoiMjA6MDA6MDAiO3M6NzoiYWRkcmVzcyI7czozNToi0YPQu9C40YbQsCDQn9GA0LjRgtGL0YbQutC+0LPQviAxMDAiO3M6NDoidGltZSI7ZDo2OTUwOS4zNjk5OTk5OTk5OTU7fX1zOjEwOiJ0b3RhbF90aW1lIjtkOjcwMTA5LjM2OTk5OTk5OTk5NTt9aToyO2E6Mjp7czo2OiJwb2ludHMiO2E6MTp7aTowO2E6Njp7czo4OiJsYXRpdHVkZSI7ZDo1My45NDExMDI5OTk5OTk5OTg7czo5OiJsb25naXR1ZGUiO2Q6MjcuNzE0MDk2MDAwMDAwMDAxO3M6MTA6InRpbWVfc3RhcnQiO3M6ODoiMTg6MDA6MDAiO3M6ODoidGltZV9lbmQiO3M6ODoiMTk6MzA6MDAiO3M6NzoiYWRkcmVzcyI7czoyODoi0KHQsNC00L7QstCw0Y8g0YPQu9C40YbQsCAxOCI7czo0OiJ0aW1lIjtkOjY2MTMyLjk2MDAwMDAwMDAwNjt9fXM6MTA6InRvdGFsX3RpbWUiO2Q6NjY3MzIuOTYwMDAwMDAwMDA2O31pOjM7YToyOntzOjY6InBvaW50cyI7YToxOntpOjA7YTo2OntzOjg6ImxhdGl0dWRlIjtkOjUzLjg1NjY4OTAwMDAwMDAwMztzOjk6ImxvbmdpdHVkZSI7ZDoyNy40MzM4MztzOjEwOiJ0aW1lX3N0YXJ0IjtzOjg6IjE4OjAwOjAwIjtzOjg6InRpbWVfZW5kIjtzOjg6IjE4OjQ1OjAwIjtzOjc6ImFkZHJlc3MiO3M6Mjk6ItGD0LvQuNGG0LAg0KDQsNGE0LjQtdCy0LAgMTEzIjtzOjQ6InRpbWUiO2Q6NjY0NTYuMTE5OTk5OTk5OTk1O319czoxMDoidG90YWxfdGltZSI7ZDo2NzA1Ni4xMTk5OTk5OTk5OTU7fX0=');

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
