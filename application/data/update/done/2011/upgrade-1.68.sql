-- Database upgrade v1.68
-- @author vpriem
-- @since 12.11.2010

ALTER TABLE `emails`
    ADD `status` TINYINT NOT NULL COMMENT '0 = nicht gesendet; 1 = gesendet' AFTER `attachment`,
    ADD `error` VARCHAR(255) NOT NULL AFTER `status`;

UPDATE `emails` SET `status` = 1;