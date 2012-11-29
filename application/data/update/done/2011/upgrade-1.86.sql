-- Database upgrade v1.86
-- @author alex
-- @since 16.12.2010

alter table restaurants add column `menuUpdateTime` date DEFAULT NULL;
