ALTER TABLE restaurant_plz ADD COLUMN `comment` VARCHAR(255);

CREATE TABLE `salesperson_company` (
  `id` int(11) NOT NULL auto_increment,
  `salespersonId` int(11) not null,
  `companyId` int(11) not null,
  `comission` int(3) not null,
  `created` timestamp default current_timestamp,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
