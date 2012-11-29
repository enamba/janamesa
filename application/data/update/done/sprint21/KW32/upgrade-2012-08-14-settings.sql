drop table if exists settings;
create table `settings` ( 
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
 `setting` VARCHAR(255) NOT NULL,
 `value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

alter table `settings` add unique(`setting`);

insert into `settings` (setting, `value`) values('maintenance', 'off');