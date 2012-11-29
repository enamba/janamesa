-- Database upgrade v1.82
-- @author alex
-- @since 09.12.2010

ALTER TABLE restaurants ADD COLUMN `isLogo` TINYINT(4) default 0;