/**
* Send the billing to the restaurant
* values: none, email, fax, all
*/
ALTER TABLE restaurants ADD COLUMN `billNotify` CHAR(5);