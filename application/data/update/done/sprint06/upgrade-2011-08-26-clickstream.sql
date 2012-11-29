drop table if exists mining_clickstream;
create table `order_clickstream` (  
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
 `url` VARCHAR(255) NOT NULL,
 `hit` timestamp NULL DEFAULT NULL,
 `orderId` INT NOT NULL,
 `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE order_clickstream add index(`orderId`);