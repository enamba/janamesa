ALTER TABLE `paypal_transactions`
    ADD `token` VARCHAR(255)  NULL  AFTER `payerId`,
    ADD `tokenValid` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `token`,
    ADD INDEX `idxToken` (`token`);
