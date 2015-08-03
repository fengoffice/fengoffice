<?php
/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */

require_once("../Connection.php");
	
class Tables {

	private $base= null;

	public function __construct() {
		
		$this->base= new Connection();	
		
	}
	
	public function __destruct(){
				
	}

	public function execute() {
	
		$sql= "CREATE DATABASE IF NOT EXISTS opengoo;
			   USE opengoo;";
		
		mysql_query($sql);
		
		$sql= "DROP TABLE IF EXISTS `excel`.`books`;
			CREATE TABLE  `excel`.`books` (
	  		`BookId` int(10) unsigned NOT NULL auto_increment,
	  		`BookName` varchar(45) NOT NULL,
	  		`UserId` int(10) unsigned NOT NULL COMMENT 'Book Owner',
	  		PRIMARY KEY  (`BookId`)
			) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=latin1 COMMENT='System Workbooks';";
		
		mysql_query($sql);
		
		$sql= "DROP TABLE IF EXISTS `excel`.`cells`;
			CREATE TABLE  `excel`.`cells` (
	  		`SheetId` int(10) unsigned NOT NULL,
	  		`DataColumn` int(10) unsigned NOT NULL,
	  		`DataRow` int(10) unsigned NOT NULL,
	  		`CellFormula` varchar(255) default NULL,
	  		`FontStyleId` int(10) unsigned NOT NULL default '0',
	  		`LayoutStyleId` int(11) NOT NULL default '0',
	  		PRIMARY KEY  (`SheetId`,`DataColumn`,`DataRow`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Sheet data';";
		
		mysql_query($sql);
		
		$sql= "DROP TABLE IF EXISTS `excel`.`fontStyles`;
			CREATE TABLE  `excel`.`fontStyles` (
	  		`FontStyleId` int(11) NOT NULL auto_increment,
	  		`BookId` int(11) NOT NULL,
	  		`FontId` int(11) NOT NULL,
	  		`FontSize` decimal(8,1) NOT NULL default '10.0',
	  		`FontBold` tinyint(1) NOT NULL default '0',
	  		`FontItalic` tinyint(1) NOT NULL default '0',
	  		`FontUnderline` tinyint(1) NOT NULL default '0',
	  		`FontColor` varchar(6) NOT NULL,
	  		PRIMARY KEY  (`FontStyleId`)
			) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;";
	
		mysql_query($sql);		
		
		$sql= "DROP TABLE IF EXISTS `excel`.`fonts`;
			CREATE TABLE  `excel`.`fonts` (
	  		`FontId` int(11) NOT NULL auto_increment,
	  		`FontName` varchar(63) NOT NULL,
	  		PRIMARY KEY  (`FontId`)
			) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;";
	
		mysql_query($sql);		
		
		$sql= "DROP TABLE IF EXISTS `excel`.`sheets`;
			CREATE TABLE  `excel`.`sheets` (
	  		`SheetId` int(10) unsigned NOT NULL auto_increment,
	  		`BookId` int(10) unsigned NOT NULL,
	  		`SheetName` varchar(45) NOT NULL,
	  		`SheetIndex` int(10) unsigned NOT NULL,
	  		PRIMARY KEY  (`SheetId`)
			) ENGINE=InnoDB AUTO_INCREMENT=1022 DEFAULT CHARSET=latin1 COMMENT='Workbooks Sheets';";
	
		mysql_query($sql);
		
		$sql= "DROP TABLE IF EXISTS `excel`.`userbooks`;
			CREATE TABLE  `excel`.`userbooks` (
	  		`UserBookId` int(10) unsigned NOT NULL auto_increment,
	  		`UserId` int(10) unsigned NOT NULL,
	  		`BookId` int(10) unsigned NOT NULL,
	  		PRIMARY KEY  (`UserBookId`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		
		mysql_query($sql);
		
		$sql= "DROP TABLE IF EXISTS `excel`.`users`;
			CREATE TABLE  `excel`.`users` (
	  		`UserId` int(10) unsigned NOT NULL auto_increment,
	  		`UserName` varchar(45) NOT NULL,
	  		`UserLastName` varchar(45) NOT NULL,
	  		`UserNickname` varchar(45) NOT NULL,
	  		`UserPassword` varchar(45) NOT NULL,
	  		`LanguageId` int(10) unsigned NOT NULL,
	  		PRIMARY KEY  (`UserId`)
			) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Sytem Users';";
		
		mysql_query($sql);
		
		$sql= "LOCK TABLES `fonts` WRITE;
			INSERT INTO `excel`.`fonts` VALUES  (1,'Arial'),
 			(2,'Times New Roman'),
 			(3,'Verdana'),
 			(4,'Courier'),
 			(5,'Lucida Sans Console'),
 			(6,'Tahoma');
			UNLOCK TABLES;";
		
		mysql_query($sql);
		
		$sql= "LOCK TABLES `fontStyles` WRITE;
			INSERT INTO `excel`.`fontStyles` VALUES  (1,247,0,'10.0',0,0,0,'#00000'),
 			(2,247,0,'10.0',0,0,0,'#00000');
			UNLOCK TABLES;";
		
		mysql_query($sql);
		
		
		
	}
		
}
	
?>

<?php

	$table= new Tables();
	
	$table->execute();

?>


