-- Database upgrade v1.77
-- @author vpriem
-- @since 29.11.2010

ALTER TABLE `links_restaurant`
    ADD `manual` TINYINT NOT NULL AFTER `restaurantId`,
    DROP `updated`;

UPDATE `links_restaurant`
    SET `manual` = 1;

ALTER TABLE `links_categories` DROP `updated`;
ALTER TABLE `links_from` DROP `updated`;
ALTER TABLE `links_list` DROP `updated`;
ALTER TABLE `links_navigation` DROP `updated`;
ALTER TABLE `links_related` DROP `updated`;
ALTER TABLE `links_to` DROP `updated`;
