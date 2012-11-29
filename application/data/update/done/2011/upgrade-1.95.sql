-- Database upgrade v1.95
-- @author Felix Haferkorn <haferkorn@lieferando.de>
-- @since 16.01.2011

CREATE TABLE `order_fraud_matching` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `prename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `streetHausNr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `plz` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `paymentName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentNumber` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paypalId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

