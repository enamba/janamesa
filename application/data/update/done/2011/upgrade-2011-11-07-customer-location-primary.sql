-- @author mlaug
-- allow the customer to pick a primary location
alter table locations add column `primary` tinyint(4) default 0;