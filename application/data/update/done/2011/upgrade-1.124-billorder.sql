alter table billing modify column mode varchar(255) not null;
alter table orders add column billOrder INT DEFAULT NULL after billRest;
alter table customers drop column customerNr;