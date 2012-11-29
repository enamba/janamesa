-- Database upgrade v1.101
-- @author vpriem
-- @since 27.01.2011

ALTER TABLE `links`
    ADD `payments` TINYINT NOT NULL DEFAULT 0 AFTER `scriptBottom`;
