-- database upgrade 1.79
-- @author alex
-- @since 02.12.2010
-- official holidays in federal lands

CREATE TABLE `restaurant_openings_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `landId` INT(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;