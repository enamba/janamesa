alter table orders add column paymentAddition varchar(50) default null;
update orders set paymentAddition='ec' where ec=1;
alter table orders drop column ec;

CREATE TABLE `restaurant_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurantId` int(11) NOT NULL,
  `payment` varchar(50) NOT NULL,
  `status` tinyint(4) DEFAULT '1',
  `default` tinyint(4) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurantId` (`restaurantId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;