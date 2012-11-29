/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.3
 * Remove an index
 *
 * @author vpriem
 */
ALTER TABLE `meals`
    DROP INDEX `idxDescription`;