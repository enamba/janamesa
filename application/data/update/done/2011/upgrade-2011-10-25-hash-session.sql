-- @author mlaug
-- @since 26.10.2011
truncate `session`;
alter table `session` modify column id char(128) not null;