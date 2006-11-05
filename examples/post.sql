-- phpMyAdmin SQL Dump
-- version 2.7.0-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Nov 05, 2006 at 09:29 AM
-- Server version: 5.0.19
-- PHP Version: 5.1.4
-- 
-- Database: `simpl_example`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `post`
-- 

CREATE TABLE `post` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `is_published` tinyint(1) unsigned NOT NULL,
  `date_entered` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `author` varchar(32) default NULL,
  `category` varchar(32) default NULL,
  `title` varchar(48) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Used to keep track of all the blog posts';
