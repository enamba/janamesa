/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.16
 *
 * @author mlaug
 */


CREATE TABLE `worker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job` varchar(255) CHARACTER SET utf8 NOT NULL,
  `args` text not null,
  `scheduled` timestamp DEFAULT 0,
  `status` int(4) default 0,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
