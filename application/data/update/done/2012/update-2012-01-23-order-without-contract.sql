-- new flag in orders
alter table orders add column contract tinyint(4) default 1 after billRest;