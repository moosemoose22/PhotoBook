-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 12, 2014 at 01:27 PM
-- Server version: 5.1.58
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `orent_info_-_zion`
--

-- --------------------------------------------------------

--
-- Table structure for table `BookArticleLangs`
--
-- Creation: Aug 05, 2013 at 10:57 AM
-- Last update: Feb 07, 2014 at 11:34 AM
--

DROP TABLE IF EXISTS `BookArticleLangs`;
CREATE TABLE IF NOT EXISTS `BookArticleLangs` (
  `BookArticleLangID` int(11) NOT NULL AUTO_INCREMENT,
  `BookArticleID` int(11) NOT NULL,
  `BookArticleLangTitle` varchar(100) NOT NULL,
  `BookArticleLangAuthor` varchar(100) NOT NULL,
  `BookArticleLangText` text NOT NULL,
  `BookLangID` char(6) NOT NULL,
  PRIMARY KEY (`BookArticleLangID`),
  KEY `BookLangID` (`BookLangID`),
  KEY `BookArticleID` (`BookArticleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100019 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookArticles`
--
-- Creation: Jul 23, 2013 at 02:29 PM
-- Last update: Aug 08, 2013 at 08:27 PM
--

DROP TABLE IF EXISTS `BookArticles`;
CREATE TABLE IF NOT EXISTS `BookArticles` (
  `BookArticleID` int(11) NOT NULL AUTO_INCREMENT,
  `BookID` int(11) NOT NULL,
  `BookArticleIsShared` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Allow multiple copies throughout book',
  PRIMARY KEY (`BookArticleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookLanguages`
--
-- Creation: Dec 26, 2012 at 09:55 PM
-- Last update: Dec 26, 2012 at 09:55 PM
--

DROP TABLE IF EXISTS `BookLanguages`;
CREATE TABLE IF NOT EXISTS `BookLanguages` (
  `BookLangID` char(6) NOT NULL,
  `BookLangName` varchar(20) NOT NULL,
  PRIMARY KEY (`BookLangID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `BookPageArticles`
--
-- Creation: Jul 18, 2013 at 12:33 PM
-- Last update: Jan 30, 2014 at 11:00 AM
--

DROP TABLE IF EXISTS `BookPageArticles`;
CREATE TABLE IF NOT EXISTS `BookPageArticles` (
  `BookPageArticleID` int(11) NOT NULL AUTO_INCREMENT,
  `BookPageID` int(11) NOT NULL,
  `BookArticleID` int(11) NOT NULL,
  `BookPageArticleInstanceNum` int(11) NOT NULL DEFAULT '0',
  `BookPageArticleIpadOrientation` varchar(10) NOT NULL COMMENT 'horizontal, vertical, outside',
  `BookPageArticleXCoord` int(11) NOT NULL DEFAULT '0',
  `BookPageArticleYCoord` int(11) NOT NULL DEFAULT '0',
  `BookPageArticleWidth` int(11) NOT NULL DEFAULT '0',
  `BookPageArticleHeight` int(11) NOT NULL DEFAULT '0',
  `BookPageArtLandscapeX` int(11) NOT NULL,
  `BookPageArtLandscapeY` int(11) NOT NULL,
  `BookPageArtPortraitX` int(11) NOT NULL,
  `BookPageArtPortraitY` int(11) NOT NULL,
  `BookPageArticleStackOrder` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`BookPageArticleID`),
  KEY `BookPageID` (`BookPageID`),
  KEY `BookArticleID` (`BookArticleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookPagePhotos`
--
-- Creation: Jul 10, 2013 at 03:17 PM
-- Last update: Feb 12, 2014 at 09:31 AM
--

DROP TABLE IF EXISTS `BookPagePhotos`;
CREATE TABLE IF NOT EXISTS `BookPagePhotos` (
  `BookPagePhotoID` int(11) NOT NULL AUTO_INCREMENT,
  `BookPageID` int(11) NOT NULL,
  `BookPhotoID` int(11) NOT NULL,
  `BookPagePhotoInstanceNum` int(11) NOT NULL DEFAULT '0',
  `BookPagePhotoIpadOrientation` varchar(10) DEFAULT NULL COMMENT 'horizontal, vertical, outside',
  `BookPagePhotoXCoord` int(11) NOT NULL,
  `BookPagePhotoYCoord` int(11) NOT NULL,
  `BookPagePhotoWidth` int(11) NOT NULL DEFAULT '0',
  `BookPagePhotoHeight` int(11) NOT NULL DEFAULT '0',
  `BookPagePhotoStretchToFill` tinyint(1) NOT NULL DEFAULT '0',
  `BookPagePhotoMoved` tinyint(4) NOT NULL,
  `BookPagePhotoStackOrder` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`BookPagePhotoID`),
  KEY `BookPageID` (`BookPageID`),
  KEY `BookPhotoID` (`BookPhotoID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=90 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookPages`
--
-- Creation: Dec 23, 2012 at 04:36 PM
-- Last update: Jan 05, 2013 at 10:18 PM
-- Last check: Dec 23, 2012 at 04:36 PM
--

DROP TABLE IF EXISTS `BookPages`;
CREATE TABLE IF NOT EXISTS `BookPages` (
  `BookPageID` int(11) NOT NULL AUTO_INCREMENT,
  `BookID` int(11) NOT NULL,
  `BookPageNum` int(11) NOT NULL,
  PRIMARY KEY (`BookPageID`),
  KEY `BookID` (`BookID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100021 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookPageStackOrder`
--
-- Creation: Jul 29, 2013 at 09:27 PM
-- Last update: Feb 12, 2014 at 09:30 AM
--

DROP TABLE IF EXISTS `BookPageStackOrder`;
CREATE TABLE IF NOT EXISTS `BookPageStackOrder` (
  `BookPageStackOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `BookPageStackOrderTableName` varchar(20) NOT NULL COMMENT 'BookPageArticles or BookPagePhotos',
  `BookPageStackOrderTableID` int(11) NOT NULL,
  `BookPageStackOrderVal` int(11) NOT NULL,
  PRIMARY KEY (`BookPageStackOrderID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookPhotos`
--
-- Creation: Jul 10, 2013 at 12:22 PM
-- Last update: Jan 13, 2014 at 06:13 AM
--

DROP TABLE IF EXISTS `BookPhotos`;
CREATE TABLE IF NOT EXISTS `BookPhotos` (
  `BookPhotoID` int(11) NOT NULL AUTO_INCREMENT,
  `BookLoginUsername` varchar(22) NOT NULL,
  `BookID` int(11) DEFAULT NULL,
  `BookPhotoURL` varchar(100) NOT NULL,
  `BookPhotoCaption` varchar(200) NOT NULL,
  `BookPhotoAddress` varchar(100) NOT NULL,
  `BookPhotoLat` float NOT NULL,
  `BookPhotoLong` float NOT NULL,
  `BookPhotoDate` datetime NOT NULL,
  `BookPhotoWidth` int(11) NOT NULL,
  `BookPhotoHeight` int(11) NOT NULL,
  `BookPhotoWidthSmall` int(11) NOT NULL DEFAULT '0',
  `BookPhotoHeightSmall` int(11) NOT NULL DEFAULT '0',
  `BookUploadDate` varchar(25) NOT NULL,
  `BookPhotoIsVisible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'BOOLEAN is an alias for TINYINT(1), so I just did it this way',
  PRIMARY KEY (`BookPhotoID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=52 ;

-- --------------------------------------------------------

--
-- Table structure for table `Books`
--
-- Creation: Jan 07, 2013 at 12:09 PM
-- Last update: Jan 07, 2013 at 12:09 PM
--

DROP TABLE IF EXISTS `Books`;
CREATE TABLE IF NOT EXISTS `Books` (
  `BookID` int(11) NOT NULL AUTO_INCREMENT,
  `BookTitle` varchar(80) NOT NULL,
  `BookLoginUsername` varchar(22) DEFAULT NULL,
  `BookLangID` char(6) DEFAULT NULL,
  PRIMARY KEY (`BookID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `BookUsers`
--
-- Creation: Jan 07, 2013 at 11:59 AM
-- Last update: Jun 04, 2013 at 01:02 PM
--

DROP TABLE IF EXISTS `BookUsers`;
CREATE TABLE IF NOT EXISTS `BookUsers` (
  `BookLoginUsername` varchar(22) NOT NULL DEFAULT '',
  `BookLoginPassword` varchar(50) DEFAULT NULL,
  `BookLoginDefaultBookID` int(11) DEFAULT NULL,
  `BookLoginDefaultLangID` char(6) DEFAULT NULL,
  PRIMARY KEY (`BookLoginUsername`),
  KEY `BookLoginDefaultBookID` (`BookLoginDefaultBookID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
