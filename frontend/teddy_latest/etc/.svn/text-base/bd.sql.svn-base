-- phpMyAdmin SQL Dump
-- version 3.3.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2010 at 03:50 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `teddy`
--

-- --------------------------------------------------------

--
-- Table structure for table `Aviso`
--

CREATE TABLE IF NOT EXISTS `Aviso` (
  `fecha` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `aviso` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Concurso`
--

CREATE TABLE IF NOT EXISTS `Concurso` (
  `CID` int(11) NOT NULL auto_increment,
  `Titulo` tinytext character set latin1 collate latin1_spanish_ci NOT NULL,
  `Descripcion` text NOT NULL,
  `Inicio` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Final` timestamp NOT NULL default '0000-00-00 00:00:00',
  `Problemas` varchar(512) NOT NULL,
  `Owner` varchar(512) NOT NULL,
  PRIMARY KEY  (`CID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Table structure for table `Ejecucion`
--

CREATE TABLE IF NOT EXISTS `Ejecucion` (
  `execID` bigint(20) NOT NULL auto_increment,
  `LANG` varchar(8) NOT NULL,
  `userID` varchar(128) NOT NULL,
  `probID` int(11) NOT NULL,
  `status` varchar(64) NOT NULL default 'JUDGING',
  `tiempo` double NOT NULL default '0',
  `remoteIP` varchar(16) NOT NULL,
  `fecha` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `Concurso` int(11) NOT NULL default '-1',
  PRIMARY KEY  (`execID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7102715033 ;

-- --------------------------------------------------------

--
-- Table structure for table `LostPassword`
--

CREATE TABLE IF NOT EXISTS `LostPassword` (
  `ID` smallint(6) NOT NULL auto_increment,
  `Fecha` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `IP` varchar(16) collate latin1_bin NOT NULL,
  `userID` varchar(64) collate latin1_bin NOT NULL,
  `Token` varchar(128) collate latin1_bin NOT NULL,
  `mailSent` tinyint(1) NOT NULL default '0' COMMENT 'Se ha enviado el correo a este usuario',
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_bin AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `Mensaje`
--

CREATE TABLE IF NOT EXISTS `Mensaje` (
  `id` int(11) NOT NULL auto_increment COMMENT 'Identificador del mensaje',
  `unread` int(1) NOT NULL default '1',
  `de` varchar(11) NOT NULL COMMENT 'Id del usuario que envia',
  `para` varchar(11) NOT NULL COMMENT 'Id del usuario que recibe',
  `mensaje` text NOT NULL COMMENT 'Mensaje',
  `fecha` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

-- --------------------------------------------------------

--
-- Table structure for table `Problema`
--

CREATE TABLE IF NOT EXISTS `Problema` (
  `probID` int(11) NOT NULL auto_increment COMMENT 'id del problema',
  `publico` varchar(4) NOT NULL default 'NO' COMMENT 'mostrar en problemas',
  `titulo` text NOT NULL,
  `problema` longtext NOT NULL,
  `tiempoLimite` int(11) NOT NULL COMMENT 'tiempo limite en segundos',
  `vistas` int(11) NOT NULL default '0',
  `aceptados` int(11) NOT NULL default '0',
  `intentos` int(11) NOT NULL default '0',
  PRIMARY KEY  (`probID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Problemas para el Juez Teddy' AUTO_INCREMENT=71 ;

-- --------------------------------------------------------

--
-- Table structure for table `Usuario`
--

CREATE TABLE IF NOT EXISTS `Usuario` (
  `userID` varchar(64) NOT NULL,
  `nombre` varchar(128) character set latin1 collate latin1_spanish_ci NOT NULL,
  `pswd` varchar(256) NOT NULL,
  `solved` int(11) NOT NULL default '0',
  `tried` int(11) NOT NULL default '0',
  `ubicacion` varchar(64) NOT NULL,
  `escuela` text NOT NULL,
  `mail` varchar(64) NOT NULL,
  `cuenta` varchar(16) NOT NULL default 'USER',
  `twitter` varchar(64) NOT NULL,
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

