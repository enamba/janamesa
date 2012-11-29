
CREATE TABLE `rabatt_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `codeEmail` varchar(255) NOT NULL,
  `codeTel` varchar(255) NOT NULL,
  `rabattId` int(11) DEFAULT NULL,
  `customerId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX(`email`,`tel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;