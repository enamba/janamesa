/**
 * Database upgrade v1.0
 *
 * @author vpriem
 */
CREATE TABLE `version` (
`version` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
`release` TIMESTAMP NOT NULL
) ENGINE = InnoDB;
