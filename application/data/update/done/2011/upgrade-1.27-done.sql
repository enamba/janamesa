/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.25
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */

ALTER TABLE customer_fidelity_newsletter MODIFY `type` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE customer_fidelity_newsletter ADD COLUMN `ordertime` TIMESTAMP NULL DEFAULT NULL AFTER `type`;

/**
 * ATTTENTION: put INDEXes into your created tables
 */