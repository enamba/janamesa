drop table if exists `orders_provission`;
create table `orders_provission` ( 
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    `orderId` INT NOT NULL,
    `prov` DECIMAL(5,2) DEFAULT 0,
    `item` DECIMAL(5,2) DEFAULT 0,
    `fee` DECIMAL(5,2) DEFAULT 0
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

alter table `orders_provission` add index(`orderId`);