-- @author mlaug
-- store profile image
alter table customers add column profileImage varchar(255) default null;

-- @author Felix Haferkorn <haferkorn@lieferando.de>
-- @since 15.11.2011
-- add field nickname (used for iPhoneApp v2 and in future in Web-Frontend)
alter table customers add column nickname varchar(255) null default null;