-- @author Alex Vait <vait@lieferando.de>
-- @since 03.04.12
-- @extras with 0 euro cost will be deleted, extras with 1 cent price will be set to 0 and are visible in frontend


delete from meal_extras_relations where cost=0;

update meal_extras_relations set cost=0 where cost=1;