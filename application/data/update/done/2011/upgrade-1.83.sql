-- Database upgrade v1.83
-- @author vpriem
-- @since 14.12.2010

CREATE TABLE `restaurant_benchmark` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ort` VARCHAR(150) NOT NULL,
    `restaurants` INT UNSIGNED NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL,
    UNIQUE `uqOrt` (`ort`)
) ENGINE = InnoDB;