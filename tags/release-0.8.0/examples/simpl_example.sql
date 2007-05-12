-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-2ubuntu1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: May 11, 2007 at 09:09 PM
-- Server version: 5.0.38
-- PHP Version: 5.2.1
-- 
-- Database: `simpl_example`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `author`
-- 

DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `author_id` int(10) unsigned NOT NULL auto_increment,
  `date_entered` datetime NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `email` varchar(48) NOT NULL,
  PRIMARY KEY  (`author_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Used to keep track of all the blog authors';

-- --------------------------------------------------------

-- 
-- Table structure for table `post`
-- 

DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('Draft','Published') NOT NULL,
  `date_entered` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `author_id` int(10) unsigned NOT NULL default '0',
  `category` varchar(32) default NULL,
  `title` varchar(48) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Used to keep track of all the blog posts';

-- --------------------------------------------------------

-- 
-- Table structure for table `session`
-- 

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `ses_id` varchar(32) NOT NULL,
  `last_access` int(12) unsigned NOT NULL,
  `ses_start` int(12) unsigned NOT NULL,
  `ses_value` text NOT NULL,
  PRIMARY KEY  (`ses_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Used to store the sessions data';
