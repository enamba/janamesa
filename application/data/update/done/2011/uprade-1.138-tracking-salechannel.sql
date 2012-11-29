
-- @author mlaug
-- @since 23.05.2011
-- @description salechannel hits

DROP TABLE IF EXISTS `salechannel_hits`;
CREATE TABLE `salechannel_hits` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `salechannel` VARCHAR(255) NOT NULL,
    `subSalechannel` VARCHAR(255) DEFAULT NULL,
    `page` VARCHAR(255) DEFAULT NULL,
    `hit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;