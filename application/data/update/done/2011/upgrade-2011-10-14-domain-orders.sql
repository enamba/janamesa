--
-- update the orders so that we store the domain, from where the order has been placed
-- this should replace the satellite field

alter table orders change `satellite` `domain` varchar(255) default null;