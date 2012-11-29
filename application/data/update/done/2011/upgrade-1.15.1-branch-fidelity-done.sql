/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.13
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */

CREATE TABLE `customer_fidelity_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(80) CHARACTER SET utf8 NOT NULL,
  `transaction` varchar(50) CHARACTER SET utf8 NOT NULL,
  `orderId` int(11) DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fkOrderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `customer_fidelity_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(80) CHARACTER SET utf8 NOT NULL,
  `points` int(4) DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `customer_fidelity_newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(80) CHARACTER SET utf8 NOT NULL,
  `type` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE customers ADD COLUMN `newsletterfidelity` TINYINT(1) NOT NULL DEFAULT '0' AFTER `newsletter`;
ALTER TABLE customers ADD COLUMN `newsletterspecial` TINYINT(1) NOT NULL DEFAULT '0' AFTER `newsletterfidelity`;


/**
ALTER TABLE orders_customer_notregistered MODIFY `email` varchar(50) CHARACTER SET utf8 NOT NULL;
ALTER TABLE newsletter_blacklist MODIFY `email` varchar(50) CHARACTER SET utf8 NOT NULL;
**/

