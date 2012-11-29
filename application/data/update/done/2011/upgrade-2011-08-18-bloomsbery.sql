-- ALTER TABLE `restaurants` ADD COLUMN `kommOldInt` INT NULL DEFAULT 0 after komm;
-- Update restaurants SET kommOldInt = komm;
-- ALTER TABLE `restaurants` MODIFY COLUMN `komm` DECIMAL( 5, 2 ) NOT NULL DEFAULT 0 ;
-- UPDATE restaurants SET komm = kommOldInt;

ALTER TABLE `restaurant_commission` ADD COLUMN `kommOldInt` INT NULL DEFAULT 0 after komm;
update `restaurant_commission` SET kommOldInt = komm;
ALTER TABLE `restaurant_commission` MODIFY COLUMN `komm` DECIMAL( 5, 2 ) NOT NULL DEFAULT 0 ;
UPDATE `restaurant_commission` SET komm = kommOldInt;


-- ALTER TABLE `restaurants` ADD `bloomsburys` TINYINT(3)  DEFAULT 0;