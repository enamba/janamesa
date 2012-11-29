/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.55
 *
 * @author mlaug
 */
ALTER TABLE restaurants ADD COLUMN `slots` int(11) default 0;
ALTER TABLE restaurants ADD COLUMN `slotsPeriod` int(11) default 0;
