/**
* Database upgrade v1.71
* @author vpriem
* @since 16.11.2010
*/

ALTER TABLE `links`
    ADD `scriptTop` TEXT NOT NULL AFTER `categoryId`,
    ADD `scriptBottom` TEXT NOT NULL AFTER `scriptTop`;
