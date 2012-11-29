alter table orders modify column salechannel text;
alter table orders add column searchTerm varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci default null;