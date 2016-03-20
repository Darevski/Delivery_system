-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Мар 21 2016 г., 02:27
-- Версия сервера: 5.5.47-0ubuntu0.14.04.1
-- Версия PHP: 5.6.19-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `Delivery`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Delivery_Points`
--

CREATE TABLE IF NOT EXISTS `Delivery_Points` (
  `Point_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Note` text COLLATE utf8_unicode_ci,
  `identifier_order` text COLLATE utf8_unicode_ci,
  `Street` text COLLATE utf8_unicode_ci NOT NULL,
  `House` text COLLATE utf8_unicode_ci NOT NULL,
  `Corps` text COLLATE utf8_unicode_ci,
  `Entry` text COLLATE utf8_unicode_ci,
  `flour` int(11) DEFAULT NULL,
  `flat` int(11) DEFAULT NULL,
  `phone_number` int(14) NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `Delivery_Date` date NOT NULL,
  `Order_Date` date NOT NULL,
  PRIMARY KEY (`Point_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `Delivery_Points`
--

INSERT INTO `Delivery_Points` (`Point_ID`, `Note`, `identifier_order`, `Street`, `House`, `Corps`, `Entry`, `flour`, `flat`, `phone_number`, `time_start`, `time_end`, `Delivery_Date`, `Order_Date`) VALUES
(1, NULL, '20160321#1', 'Рафиева', '113', NULL, '5', 4, 159, 2147483647, '18:30:00', '20:00:00', '2016-03-22', '2016-03-20');

-- --------------------------------------------------------

--
-- Структура таблицы `Orders`
--

CREATE TABLE IF NOT EXISTS `Orders` (
  `Order_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Point_ID` int(11) NOT NULL,
  `Description` text COLLATE utf8_unicode_ci NOT NULL,
  `Cost` double NOT NULL,
  PRIMARY KEY (`Order_ID`),
  KEY `Point_ID` (`Point_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `Orders`
--

INSERT INTO `Orders` (`Order_ID`, `Point_ID`, `Description`, `Cost`) VALUES
(1, 1, 'Товар №1', 100.25),
(2, 1, 'Товар №2', 80.25);

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `Orders_ibfk_1` FOREIGN KEY (`Point_ID`) REFERENCES `Delivery_Points` (`Point_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
