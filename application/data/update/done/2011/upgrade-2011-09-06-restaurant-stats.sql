drop table if exists restaurant_status_history;
create table `restaurant_status_history` (  
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
 `status` SMALLINT  NOT NULL,
 `count`  MEDIUMINT NOT NULL,
 `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `restaurant_status_history` ADD INDEX `idxCreated` ( `created` );