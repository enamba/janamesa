/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.19
 * @author mlaug
 */

CREATE TABLE `billing_admonation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingId` int(11) NOT NULL,
  `created` timestamp default current_timestamp,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `billing_customized` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heading` varchar(255),
  `street` varchar(255),
  `hausnr` varchar(255),
  `plz` varchar(255),
  `city` varchar(255),
  `addition` varchar(255),
  `content` varchar(255),
  `template` varchar(255),
  `verbose` tinyint(4) default 0,
  `showProject` tinyint(4) default 1,
  `showCostcenter` tinyint(4) default 1,
  `showEmployee` tinyint(4) default 1,
  `refId` int(11) NOT NULL,
  `mode` enum('rest','comp'),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

