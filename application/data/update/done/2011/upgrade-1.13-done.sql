/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.13
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */

ALTER TABLE newsletter_blacklist ADD COLUMN `comment` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER email;