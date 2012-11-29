-- user can be now assigned to many groups, not to a single one
-- @author Alex Vait <vait@lieferando.de>
-- @since 28.08.2012

drop table if exists admin_access_user_groups_nn;

create table `admin_access_user_groups_nn` ( 
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    `userId` INT(4) NOT NULL DEFAULT 0,
    `groupId` INT(4) NOT NULL DEFAULT 0,
    `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NULL DEFAULT NULL 
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

insert into admin_access_user_groups_nn (`userId`, `groupId`) select aau.id, aau.groupId from admin_access_users aau where groupId > 0;


-- later, when the stuff is live and working fine
-- alter table `admin_access_users` drop `groupId`;
