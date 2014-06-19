# phpMyAdmin SQL Dump
# version 2.5.2-rc2
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Sep 17, 2004 at 09:37 AM
# Server version: 4.0.20
# PHP Version: 4.3.6
# 
# Database : texaco
# 

# --------------------------------------------------------

#
# Table structure for table authcodes
#
# Creation: Sep 08, 2004 at 03:52 PM
# Last update: Sep 16, 2004 at 09:29 AM
#
drop table If exists authcodes;

CREATE TABLE authcodes 
(
  AuthNumber	bigint(20) NOT NULL auto_increment PRIMARY KEY,
  MerchantId	int(11) NOT NULL default '0',
  MerchantTxNo	char(20) NOT NULL default '',
  AccountNo	bigint(20) NOT NULL default '0',
  Amount	int(11) NOT NULL default '0',
  CreateDate	datetime NOT NULL default '0000-00-00 00:00:00',
  AuthStatus	char(10) NOT NULL default ''
) TYPE=MyISAM;


DROP TABLE IF EXISTS Msgref;

CREATE TABLE Msgref 
(
  id		bigint(20) NOT NULL auto_increment,
  MerchantId	int(11) NOT NULL default '0',
  Msgref	int(11) NOT NULL default '0',
  RequestCode	int(11) NOT NULL default '0',
  Response	longtext NOT NULL,
  CreateDate	datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)
) TYPE=MyISAM AUTO_INCREMENT=17 ;
  