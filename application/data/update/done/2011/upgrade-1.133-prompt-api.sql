-- vpriem
-- 05.05.2011

ALTER TABLE `courier` ADD `api` VARCHAR(255) NOT NULL;
UPDATE `courier` SET `api` = 'prompt' WHERE `id` = 4;