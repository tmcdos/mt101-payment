CREATE TABLE `firma` (
  `ID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `FIRMA` varchar(25) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251

CREATE TABLE `person` (
  `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(80) NOT NULL,
  `IBAN` varchar(25) NOT NULL,
  `ACTIVE` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Still working, or fired',
  `WRITER` tinyint(3) unsigned NOT NULL,
  `CREATED` datetime NOT NULL,
  `CHANGER` tinyint(3) unsigned NOT NULL,
  `CHANGED` datetime NOT NULL,
  `ZAPLATA` float unsigned NOT NULL,
  `EGN` char(10) NOT NULL COMMENT 'Social-security number',
  `FIRMA` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Index into table FIRMA',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `iban` (`IBAN`),
  UNIQUE KEY `egn` (`EGN`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251

CREATE TABLE `salary` (
  `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `DATUM` date NOT NULL,
  `PERSON` smallint(5) unsigned NOT NULL,
  `SUMA` float unsigned NOT NULL,
  `UPDATER` smallint(5) unsigned DEFAULT NULL,
  `CHANGED` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `DATUM` (`DATUM`,`PERSON`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251

CREATE TABLE `user` (
  `ID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `LOGIN` varchar(16) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL,
  `PASS` varchar(16) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL,
  `NAME` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`,`LOGIN`,`PASS`),
  KEY `ID_2` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251