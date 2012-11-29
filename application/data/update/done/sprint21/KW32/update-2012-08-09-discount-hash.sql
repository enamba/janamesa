-- Hash for discounts
-- @author Andre Ponert <ponert@theqserver.de>
-- @since 09.08.2012
ALTER TABLE `rabatt` ADD hash VARCHAR(32) DEFAULT NULL;
UPDATE `rabatt` SET `hash` = MD5(CONCAT('hKtER55xpuemj',id,'hKtER55xpuemj'));