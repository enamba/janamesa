ALTER TABLE `customers` add facebookPost tinyint(1) default 1 after facebookId, CHANGE COLUMN `password` `password` VARCHAR(255) NULL  ;