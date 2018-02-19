-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2016 at 03:46 AM
-- Server version: 5.5.42
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `passhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `acl`
--

CREATE TABLE `acl` (
  `id` int(11) unsigned NOT NULL,
  `groupId` int(11) unsigned NOT NULL DEFAULT '0',
  `type` enum('page','category') NOT NULL DEFAULT 'page',
  `foreignId` int(11) unsigned NOT NULL DEFAULT '0',
  `accessLevel` int(11) unsigned DEFAULT '0' COMMENT '1 = Read, 2 = Create, 4 = Edit, 8 = Delete'
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `acl`
--

INSERT INTO `acl` (`id`, `groupId`, `type`, `foreignId`, `accessLevel`) VALUES
(1, 1, 'page', 1, 8),
(2, 1, 'page', 2, 8),
(3, 1, 'page', 3, 8),
(4, 1, 'page', 4, 8),
(5, 2, 'page', 1, 4),
(6, 2, 'page', 2, 0),
(7, 2, 'page', 3, 1),
(8, 2, 'page', 4, 0),
(11, 1, 'category', 3, 0),
(12, 1, 'category', 4, 8),
(14, 2, 'category', 4, 0),
(32, 2, 'page', 1, 1),
(37, 2, 'category', 1, 1),
(39, 2, 'category', 1, 2),
(40, 2, 'category', 1, 4),
(41, 2, 'category', 1, 8),
(46, 2, 'page', 2, 1),
(48, 2, 'page', 2, 2),
(49, 2, 'page', 2, 4),
(50, 2, 'page', 2, 8),
(51, 2, 'page', 3, 2),
(52, 2, 'page', 3, 4),
(53, 2, 'page', 3, 8),
(54, 2, 'page', 4, 1),
(55, 2, 'category', 3, 2),
(60, 2, 'page', 4, 2),
(61, 2, 'page', 4, 4),
(64, 13, 'page', 3, 2),
(65, 13, 'page', 3, 8),
(66, 13, 'page', 4, 4),
(67, 2, 'page', 4, 8);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `sorting` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `sorting`, `user_id`) VALUES
(1, 'General', 1, 0),
(2, 'My Logins', 0, 1),
(3, 'Test', 2, 0),
(4, 'Test 2', 3, 0),
(5, 'My Logins', 0, 7);

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `type` enum('text','password','textarea','') NOT NULL,
  `sorting` int(10) unsigned NOT NULL DEFAULT '0',
  `login_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`, `value`, `type`, `sorting`, `login_id`) VALUES
(3, 'username', '5457c4d85de0955b9f4d402bfde0cb447e2b3c80e0dbfd5873268b4d2b2f4cbcca73d4437496179af2443ffbadce46480689de57a6b9bf1d700ae4bb82a05c1d', 'text', 0, 2),
(4, 'password', '012f897e5a0ebe4bf1f94035159e253881ec68f3eee3e167dffb1e549c6ebed25d21c3823093da7ae6e55672a3dc02a3435a000aa568115acc85736a252b26a2', 'password', 1, 2),
(5, 'username', 'df24fb7015a55b1a2728837472ba789147bb8434fdf48c14f35bf485829fad029bce5e63f3ed80d3f75b068599298a8b70fd17bc42c82ed0306b812c80086801', 'text', 0, 3),
(6, 'password', '96f3e9d5beeed08512cd1190934d29acc6e2fd442dfc818417c4e3575eaf54949d225df991ffeaff6c7264caf6af340e5869a6d3b53af6ef5d7420bee7dec4fd', 'textarea', 1, 3),
(9, 'username', '83e53c7f198fb2a42450c2b0ac9058e37a75f8659640c2b1422b9fe95bdd51d577e77be9df60ba0e168d106b2fe1a6a6f351fcea9e9e977f98777625bd456ba7', 'text', 0, 5),
(10, 'password', '73bb1cf72e782cb8a3c1e633a53698f5bcf6bf920ccfee6e3ae5549b81eea255b4b41aa596d1a548e2627825d0a63523758428286fc1872c12752189b32600cc', 'password', 1, 5),
(11, 'url', 'ad6c2d4b04db364c0010812d54b3783ddc2ce2b070caf19d8bc153d65bc2531f85d72dcdc303c76dd3cba4a17166ef3360f5c771820c79122d20bca4ae7d6c65f15ea5964bc22276e8d6017adf54bde9', 'text', 0, 6),
(12, 'username', '32cce885422ea5375cfa29812103b048fe211b45e82006de58a02f47350f897bb9bcdef6326598f6bcf1c1ed30f1f4da86971ea80dbb42d9c39cb7da91cc506d', 'text', 1, 6),
(13, 'password', '062e89fd985678ae31c513c7a1203ffaa0f59261a1293db7993d7c88e7ecd12f13a743df832ef00e028c5711b12ab4f58bf02182835e97732aa3e59b7d9fc78c', 'password', 2, 6),
(14, 'username', '78103bc96768eec65185b0996df47b0d7a6d376840123a0ea7a072c429ad77cd3f46730210cbc06f740e715eaf7395892ae0f131c6546f86792786eb84a85248', 'text', 0, 7),
(18, 'new-field', '8fa2d87ed003ce9d6882c6db81d60841689bcc3a71387dc5465936584e6e1cd9abb91fc98a40cec62e03a6194d2a671aed5ebb574b630ca584a0982c30ec730f', 'text', 1, 7),
(26, 'username', 'c993b51bc282ce63020c451a082fca28de71a1f3bc09e4c4cbe6d6377d20c57f0f4869224ec2e0b82883010d16d1b95ef6f96e11a7724c2045ea0d1a32d90720', 'text', 0, 11),
(27, 'password', '29999c195706faed7a86bf90d51450d8994db9f4dce98078abad33b8c584a871803789d755d340a8fa14eb1cfd92b5ff1ea9f98bee03a031bb54aaacb5b0f55e', 'password', 1, 11),
(28, 'new-field', '36feaaf2432bf50f10b19dee72b3b0d3a07273cb17ff5f617a3572231fbc63262fe79e062d82e3505002ce09636a49d4bf25f02f20fe94b0e958663a11248f08', 'text', 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'User'),
(13, 'New Group');

-- --------------------------------------------------------

--
-- Table structure for table `logins`
--

CREATE TABLE `logins` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `category_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `logins`
--

INSERT INTO `logins` (`id`, `name`, `category_id`) VALUES
(2, 'Untitled Edit', 1),
(3, 'Hello!', 5),
(5, 'Admin Login', 2),
(6, 'Facebook', 3),
(7, 'Twitter', 3),
(11, 'Some Login in Test 4 Category Bro', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`) VALUES
(1, 'Logins'),
(2, 'Categories'),
(3, 'Users'),
(4, 'Groups');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `csrf` text NOT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `stamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `data`, `csrf`, `ip`, `agent`, `stamp`) VALUES
('13609aac05ca643766de5667860964c9', 'csrf|s:25:"jfkg8xqr2097.2sou22johofp";user_name|s:3:"Joe";logged_in|i:1;group_id|s:1:"2";user_id|s:1:"2";', 'jfkg8xqr2097.wjmfh7tzs92c', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:49.0) Gecko/20100101 Firefox/49.0', 1481423617),
('99b62ac2d18c4f3afa303205450ae192', 'csrf|s:26:"jfkg8xqr2097.2w8qfz5crps0c";logged_in|i:1;group_id|s:1:"1";user_id|s:1:"1";user_name|s:5:"Admin";', 'jfkg8xqr2097.16wppukcdjhg7', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36', 1481423365);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `groupId` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `resetKey` varchar(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `groupId`, `name`, `email`, `password`, `resetKey`) VALUES
(1, 1, 'Admin', 'admin@passhub.io', '$2y$14$b1a481be3c9cb3c868cb4uvVAFKnUidYZOu05TMLTF0Lk.LBTOura', ''),
(2, 2, 'Joe', 'joe@passhub.io', '$2y$14$7bc1ba46cb8f924b96f64O/cwRoAMEYqLEsoVtWtxmck/QWCM5pYC', ''),
(7, 15, 'Bob Test Group-erson', 'hey', '$2y$14$5da3b76f34b89ed0fb798us9sbu2aBPl6/XMK1NK0HTR5mLam8cua', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupId` (`groupId`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_id` (`login_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acl`
--
ALTER TABLE `acl`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=68;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `logins`
--
ALTER TABLE `logins`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `acl`
--
ALTER TABLE `acl`
  ADD CONSTRAINT `acl to groups` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fields`
--
ALTER TABLE `fields`
  ADD CONSTRAINT `fields to logins` FOREIGN KEY (`login_id`) REFERENCES `logins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
