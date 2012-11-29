/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.x
 *
 * @author x
 */

ALTER TABLE companys ADD COLUMN `allowYdCard` tinyint(1) NOT NULL DEFAULT '0';

CREATE TABLE `company_budgets_ydcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budgetId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `grant` int(11) DEFAULT NULL,
  `markType` int(4) DEFAULT NULL,
  `markCount` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;