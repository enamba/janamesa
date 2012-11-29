/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.29
 * add floor fee to the restaurants
 * @author alex
 */

ALTER TABLE restaurants ADD COLUMN `floorFee` int(11);