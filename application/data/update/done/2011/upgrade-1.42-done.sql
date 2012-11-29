/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.42
 *
 * @author alex
 */

ALTER TABLE canteen MODIFY `deadline` VARCHAR(5) DEFAULT NULL;
ALTER TABLE `courier_plz` ADD `mincost` INT NOT NULL;
