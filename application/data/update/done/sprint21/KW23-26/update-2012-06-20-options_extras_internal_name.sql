-- @author alex
-- @since 20.06.2012

ALTER TABLE `meal_options_rows` ADD Column `internalName` varchar(255) default null after `name`;
ALTER TABLE `meal_extras_groups` 
CHANGE `name` `internalName` varchar(255) DEFAULT NULL,
ADD Column `name` varchar(255) DEFAULT NULL after `internalName`;
