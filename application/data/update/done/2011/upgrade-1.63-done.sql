/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.63
 *
 * @author alex
 */

CREATE TABLE `restaurant_notepad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `restaurantId` int NOT NULL,
  `adminId` int NOT NULL,
  `masterAdmin` int DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
)  ENGINE = InnoDB;
