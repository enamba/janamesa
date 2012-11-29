/**
* Database upgrade v1.72
* @author alex
* @since 16.11.2010
*/

/*
* State of billing asset
*/

ALTER TABLE `billing_assets`
    ADD COLUMN `billRest` INT DEFAULT NULL,
    ADD COLUMN `billCompany` INT DEFAULT NULL,
    ADD COLUMN `billCourier` INT DEFAULT NULL;

update billing_assets set billRest=99999, billCompany=99999, billCourier=99999;


/*
* Update billnig from/until times so that the format of from - until is always <date> 00:00:00 - <date> 23:59:59
*/
UPDATE billing SET billing.from=CONCAT(YEAR(billing.from), '-', MONTH(billing.from), '-', DAY(billing.from), ' 00:00:00.0') WHERE billing.from AND (SECOND(billing.from)!=0 OR MINUTE(billing.from)!=0 OR HOUR(billing.from)!=0);
UPDATE billing SET billing.until=CONCAT(YEAR(billing.until), '-', MONTH(billing.until), '-', DAY(billing.until), ' 23:59:59.0') WHERE billing.until AND (SECOND(billing.until)!=59 OR MINUTE(billing.until)!=59 OR HOUR(billing.until)!=23);

ALTER TABLE `backlinks`
    ADD `result` TEXT NOT NULL AFTER `url`,
    ADD `error` VARCHAR(255) NOT NULL AFTER `result`;

ALTER TABLE `heidelpay_transactions`
    ADD INDEX `fkOrderId` (`orderId`);