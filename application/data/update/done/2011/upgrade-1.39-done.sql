/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.39
 *
 * @author vpriem
 */
CREATE TABLE IF NOT EXISTS `courier_plz` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `courierId` INT UNSIGNED NOT NULL,
  `plz` int(5) NOT NULL,
  `deliverTime` INT NOT NULL DEFAULT '1800',
  `delcost` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fkCourierId` (`courierId`),
  KEY `idxPlz` (`plz`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;