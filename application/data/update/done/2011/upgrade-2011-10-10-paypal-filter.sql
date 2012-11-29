
ALTER TABLE `paypal_transactions` ADD `payerId` VARCHAR( 255 ) NOT NULL AFTER `response`;

/*
In php ausgelagert
ALTER TABLE `paypal_transactions` ADD `payerId` VARCHAR( 255 ) NOT NULL AFTER `response`  */

ALTER TABLE `paypal_transactions` ADD INDEX `idxPayerId` ( `payerId` ) ;

/*
create für black/white list 
*/
DROP TABLE IF EXISTS `paypal_black_white_list`;

CREATE TABLE `paypal_black_white_list` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`payerId` VARCHAR( 120 ) NOT NULL ,
`created` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`white` TINYINT NOT NULL COMMENT 'Flag für Black/White',
`comment` VARCHAR( 255 ) NOT NULL ,
`count` INT NULL,
UNIQUE (
`payerId`
)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;