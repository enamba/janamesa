/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.61
 *
 * @author alex
 */

alter table restaurants add column checked int(4) default 0;
update restaurants set checked=1 where isOnline=0 and status=0;

CREATE TABLE `restaurant_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `restaurantId` int(11) NOT NULL,
    `tag` VARCHAR(255) NOT NULL
) ENGINE = InnoDB;