SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE `attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `issueId` int(11) NOT NULL,
  `creatorId` int(11) DEFAULT NULL,
  `originalName` varchar(255) NOT NULL,
  `physicalName` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `contentType` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `change` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issueId` int(11) NOT NULL,
  `creatorId` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `data` mediumtext,
  `oldValue` mediumtext,
  `newValue` mediumtext,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `custom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issueId` int(11) NOT NULL,
  `platformId` int(11) DEFAULT NULL,
  `googleId` varchar(6) DEFAULT NULL,
  `redmineId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `trackerId` int(11) NOT NULL,
  `statusId` int(11) NOT NULL,
  `priorityId` int(11) NOT NULL,
  `creatorId` int(11) NOT NULL,
  `updaterId` int(11) DEFAULT NULL,
  `assigneeId` int(11) DEFAULT NULL,
  `categoryId` int(11) DEFAULT NULL,
  `targetId` int(11) DEFAULT NULL,
  `foundId` int(11) DEFAULT NULL,
  `importId` int(11) DEFAULT NULL,
  `title` varchar(250) NOT NULL,
  `details` mediumtext NOT NULL,
  `votes` int(11) NOT NULL,
  `closed` bit(1) NOT NULL,
  `updated` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `lastComment` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `priority` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `name` varchar(10) NOT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `isPublic` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `query` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `filter` varchar(500) DEFAULT NULL,
  `order` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leftId` int(11) NOT NULL,
  `rightId` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `creatorId` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `session` (
  `id` varchar(100) NOT NULL,
  `data` mediumtext NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  `closed` tinyint(1) NOT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `typeMask` int(11) NOT NULL,
  `email` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importId` int(11) DEFAULT NULL,
  `projectId` int(11) DEFAULT NULL,
  `name` varchar(10) NOT NULL,
  `releaseDate` date DEFAULT NULL,
  `released` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
