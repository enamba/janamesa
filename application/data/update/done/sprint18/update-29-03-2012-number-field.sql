alter table city_verbose
	add column number varchar(80) default null,
	add column number_regex varchar(255) default null;

alter table city_verbose
        modify column cityId INT not null;

alter table city_verbose add index(`cityId`);