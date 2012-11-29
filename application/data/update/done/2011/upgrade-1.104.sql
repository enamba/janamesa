/**
* Database upgrade v1.104
* @author mlaug
* @since 31.01.2011
*/

CREATE TABLE `session` (
    `id` char(32),   
    `modified` INT,
    `lifetime` INT,
    `data` BLOB,
    `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
)ENGINE = InnoDB;
