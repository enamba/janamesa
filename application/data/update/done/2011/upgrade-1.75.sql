-- Database upgrade v1.75
-- @author alex
-- @since 25.11.2010

ALTER TABLE restaurant_ratings ADD `status` TINYINT NOT NULL DEFAULT 1 COMMENT '0 = offline; 1 = online' AFTER `author`;
