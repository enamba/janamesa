/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.40
 * @author mlaug
 */
update restaurant_ratings set advise=1 where quality>3 or delivery>3;
update restaurant_ratings set advise=0 where quality<=3;