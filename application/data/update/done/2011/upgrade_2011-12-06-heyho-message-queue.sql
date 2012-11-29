DROP TABLE IF EXISTS `heyho_ticket_queue`;

CREATE TABLE `heyho_ticket_queue` (
`id` INT NOT NULL AUTO_INCREMENT,
`orderId` INT NOT NULL ,
`supporter` INT NOT NULL ,
`state` INT NOT NULL ,
`kind` VARCHAR( 255 ) NOT NULL ,
`mode` VARCHAR( 255 ) NOT NULL ,
`payment` VARCHAR( 255 ) NOT NULL ,
`notifyPayed` INT NOT NULL ,
`premium` INT NOT NULL ,
`timediff` INT NOT NULL ,
`rntAllwaysCall` INT  NULL ,
`notepad_created` TIMESTAMP NULL ,
`prio` INT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MEMORY CHARACTER SET utf8 COLLATE utf8_general_ci;
