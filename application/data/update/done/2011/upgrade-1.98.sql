/**
*   Database upgrade v1.98
*   @author mlaug
*   @since 24.01.2011
*/

ALTER TABLE orders add column supporter INT DEFAULT NULL;

update orders set state=-2 where state=-5 and time < '2011-01-26 14:30:00';
update orders set state=-2 where state=-4 and time < '2011-01-26 14:30:00';
update orders set state=-2 where state=-3 and time < '2011-01-26 14:30:00';