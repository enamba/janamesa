-- @author vpriem

ALTER TABLE `ebanking_transactions` 
    ADD `payerId` VARCHAR(40) NULL AFTER `data`, 
    ADD INDEX `idxPayerId` (`payerId`);