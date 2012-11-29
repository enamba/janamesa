-- @author alex
-- @since 19.12.11
-- names for official holidays

alter table `restaurant_openings_holidays` add column `name` varchar(255) default null after `date`;
