/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.20
 * @author mlaug
 */

CREATE TABLE `options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) NOT NULL,
  `optionName` varchar(255) NOT NULL,
  `optionValue` text Default NULL,
  `created` timestamp default current_timestamp,
  UNIQUE(`hash`,`optionName`),
  INDEX `indexOptionName` (`optionName`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
