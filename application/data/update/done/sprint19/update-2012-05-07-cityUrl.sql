-- @author Matthias Laug <laug@lieferando.de>

alter table city add column cityUrl varchar(100) default null;
update city set cityUrl=
update city set cityUrl=REPLACE(cityUrl, 'ą','a');
update city set cityUrl=REPLACE(cityUrl, 'ć','c');
update city set cityUrl=REPLACE(cityUrl, 'Ć','c');
update city set cityUrl=REPLACE(cityUrl, 'ę','e');
update city set cityUrl=REPLACE(cityUrl, 'ł','l');
update city set cityUrl=REPLACE(cityUrl, 'ń','n');
update city set cityUrl=REPLACE(cityUrl, 'ó','o');
update city set cityUrl=REPLACE(cityUrl, 'Ó','o');
update city set cityUrl=REPLACE(cityUrl, 'ś','s');
update city set cityUrl=REPLACE(cityUrl, 'Ś','s');
update city set cityUrl=REPLACE(cityUrl, 'ź','z');
update city set cityUrl=REPLACE(cityUrl, 'Ź','z');
update city set cityUrl=REPLACE(cityUrl, 'Ż','z');
update city set cityUrl=REPLACE(cityUrl, 'ż','z');
update city set cityUrl=REPLACE(cityUrl, ' ','-');
alter table city add index(cityUrl);