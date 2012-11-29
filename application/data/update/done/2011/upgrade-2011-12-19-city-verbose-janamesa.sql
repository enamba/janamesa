CREATE TABLE `city_verbose` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `city` varchar(50) DEFAULT NULL,
  `street` varchar(70) DEFAULT NULL,
  `neighbour` varchar(72) DEFAULT NULL,
  `cityId` varchar(9) NOT NULL DEFAULT '',
  `tp_logradouro` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;