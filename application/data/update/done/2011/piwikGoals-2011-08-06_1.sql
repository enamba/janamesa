drop table if exists `piwikGoals`;
create table `piwikGoals` ( 
    `id` INT NOT NULL auto_increment primary key,
    `goalId` INT NOT NULL,
    `goalName` VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci