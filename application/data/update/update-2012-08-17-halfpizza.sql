ALTER TABLE `janamesa.com.br`.`meals` ADD COLUMN `priceType` ENUM('normal','options_avg','options_max') NULL DEFAULT 'normal';
DROP TABLE IF EXISTS `meal_mealoptions_nn`;
CREATE TABLE `meal_mealoptions_nn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mealId` int(10) unsigned NOT NULL,
  `optionRowId` int(10) unsigned NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fkOptionId` (`mealId`),
  KEY `fkOptionRowId` (`optionRowId`),
  KEY `idxOptionIdOptionRowId` (`mealId`,`optionRowId`),
  UNIQUE INDEX `mailoption` (`mealId` ASC, `optionRowId` ASC)
) ENGINE=InnoDB CHARSET=utf8;

DROP TABLE IF EXISTS `orders_bucket_meals_mealoptions`;
CREATE TABLE `orders_bucket_meals_mealoptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bucketItemId` int(11) NOT NULL,
  `mealId` int(11) NOT NULL,
  `cost` int(10) unsigned DEFAULT '0',
  `tax` float unsigned DEFAULT NULL,
  `count` int(10) unsigned DEFAULT '1',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT 'unbekannte Option',
  PRIMARY KEY (`id`),
  KEY `fkBucketItemId` (`bucketItemId`),
  KEY `fkOptionId` (`mealId`)
) ENGINE=InnoDB CHARSET=utf8;

