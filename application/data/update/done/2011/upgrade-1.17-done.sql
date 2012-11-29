/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.17
 *
 * @author vpriem
 */
CREATE TABLE `billing_sent` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `billingId` INT NOT NULL,
    `on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `via` VARCHAR(10) NOT NULL ,
    `to` VARCHAR(255) NOT NULL ,
    `status` TINYINT NOT NULL ,
    INDEX `fkBillingId` (`billingId`)
) ENGINE = InnoDB;