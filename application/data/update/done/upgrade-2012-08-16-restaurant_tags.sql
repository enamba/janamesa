-- Tag for restaurants no more with names, but with id and separate table for tags
-- @author Alex Vait <vait@lieferando.de>
-- @since 16.08.2012

drop table if exists tags;
create table `tags` ( 
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    `name` VARCHAR(255) NOT NULL,
    UNIQUE uqTag (`name`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

insert into tags (name) select distinct(tag) from restaurant_tags order by tag;

alter table `restaurant_tags` add `tagId` INT after `tag`;

update restaurant_tags rt join tags t on t.name=rt.tag set rt.tagId=t.id;

ALTER TABLE `restaurant_tags` DROP INDEX `uniquetags`;

-- later, when the stuff is live and working fine
-- alter table `restaurant_tags` drop `tag`;
-- alter table `restaurant_tags` drop index `uniquetags`;

