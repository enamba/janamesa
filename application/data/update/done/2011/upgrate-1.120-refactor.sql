/**
 * @author vpriem
 * @since 25.02.2011
 */
CREATE TABLE `blocked_uuid` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `uuid` Varchar(255) not null,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX `fkUuid` (`uuid`)
) ENGINE = InnoDB;