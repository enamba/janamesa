/**
  * @author mlaug
  * Upgrade fuer Umfrage
  */
CREATE TABLE `umfrage` (
    `id` INT AUTO_INCREMENT,   
    `sex` TINYINT(4),   
    `age` TINYINT(4),   
    `job` TINYINT(4),
    `repeat` TINYINT(4),
    `comment` TEXT,
    `orderId` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(`orderId`,`email`),
    PRIMARY KEY (`id`)
)ENGINE = InnoDB;

-- create index `ix_ident_clickstream` on mining_clickstream (`ident`);
-- create index `ix_ident_order` on orders (`ident`);

alter table `orders` add column pulledOn TIMESTAMP DEFAULT '0000-00-00 00:00:00';

update orders set state=-2 where state=-5 and time < '2011-02-07 17:00:00';
update orders set state=-2 where state=-4 and time < '2011-02-07 17:00:00';
update orders set state=-2 where state=-3 and time < '2011-02-07 17:00:00';
update orders o inner join restaurants r on r.id=o.restaurantId set o.state=2 where r.premium=1 and o.state=1;