-- @author mlaug
-- remove all relations to old printers

delete from restaurant_gprs_printer where brand='';
alter table restaurant_gprs_printer drop column `brand`;