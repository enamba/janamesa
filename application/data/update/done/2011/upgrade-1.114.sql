/**
* Database upgrade v1.112
* @author mlaug
* @since 14.02.2011
*/

alter table orders add column 'saleChannel' varchar(255) default 'unknown';
