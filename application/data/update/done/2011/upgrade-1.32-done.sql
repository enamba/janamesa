/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.32
 * CANTEEN
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 24.08.2010
 */
CREATE TABLE `canteen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyId` int(11) NOT NULL,
  `serviceId` int(11) NOT NULL,
  `deadline` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_company` (`companyId`),
  KEY `fk_service` (`serviceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `canteen_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `canteenId` int(11) NOT NULL,
  `mealCategoryId` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_canteen` (`canteenId`),
  KEY `fk_mealCategory` (`mealCategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE customer_company DROP COLUMN canteen;
INSERT INTO `servicetypes` (`id`, `name`, `className`) VALUES (5, 'Kantine', 'canteen');