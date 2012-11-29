-- Database upgrade v1.80
-- @author vpriem
-- @since 03.11.2010

ALTER TABLE `links`
    ADD `domain` VARCHAR(255) NOT NULL AFTER `id`,
    DROP INDEX `uqUrl`,
    ADD UNIQUE `uqUrl` (`domain` , `url`);

UPDATE `links`
    SET `domain` = 'www.yourdelivery.de';

ALTER TABLE `backlinks`
    ADD `domain` VARCHAR(255) NOT NULL AFTER `id`;

UPDATE `backlinks`
    SET `domain` = 'www.yourdelivery.de';