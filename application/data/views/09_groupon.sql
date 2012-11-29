-- @author Felix Haferkorn <haferkorn@lieferando.de>
-- @since 09.11.2010

DROP TABLE IF EXISTS `view_groupon_rabatt_codes_used`;
DROP VIEW IF EXISTS `view_groupon_rabatt_codes_used`;

CREATE VIEW `view_groupon_rabatt_codes_used`
AS select
   `rcg`.`id` AS `id`,
   `rcg`.`code` AS `code`,
   `rcg`.`sameCustomer` AS `sameCustomer`,
   `rcg`.`rabattCodeId` AS `rabattCodeId`,
   `o`.`id` AS `OrderId`,
   `o`.`time` AS `time`
from (`rabatt_codes_groupon` `rcg` join `orders` `o` on((`o`.`rabattCodeId` = `rcg`.`rabattCodeId`)))
where (`o`.`state` > 0);


DROP TABLE IF EXISTS `view_groupon_rabatt_codes_single`;
DROP VIEW IF EXISTS `view_groupon_rabatt_codes_single`;

CREATE VIEW `view_groupon_rabatt_codes_single`
AS select
   `rcg`.`id` AS `id`,
   `rcg`.`code` AS `code`,
   `rcg`.`sameCustomer` AS `sameCustomer`,
   `rcg`.`rabattCodeId` AS `rabattCodeId`,
   `rcg`.`OrderId` AS `OrderId`,
   `rcg`.`time` AS `time`
from `view_groupon_rabatt_codes_used` `rcg`
where (`rcg`.`id` in (select `view_groupon_rabatt_codes_used`.`id` AS `id` from `view_groupon_rabatt_codes_used`
where (`view_groupon_rabatt_codes_used`.`sameCustomer` > 0) group by `view_groupon_rabatt_codes_used`.`sameCustomer`) or (`rcg`.`sameCustomer` = 0));



DROP TABLE IF EXISTS `view_groupon_rabatt_codes_multi`;
DROP VIEW IF EXISTS `view_groupon_rabatt_codes_multi`;

CREATE VIEW `view_groupon_rabatt_codes_multi`
AS select
   `view_groupon_rabatt_codes_used`.`id` AS `id`,
   `view_groupon_rabatt_codes_used`.`code` AS `code`,
   `view_groupon_rabatt_codes_used`.`sameCustomer` AS `sameCustomer`,
   `view_groupon_rabatt_codes_used`.`rabattCodeId` AS `rabattCodeId`,
   `view_groupon_rabatt_codes_used`.`OrderId` AS `OrderId`,
   `view_groupon_rabatt_codes_used`.`time` AS `time`
from `view_groupon_rabatt_codes_used`
where (not(`view_groupon_rabatt_codes_used`.`id` in (select `view_groupon_rabatt_codes_single`.`id` AS `id` from `view_groupon_rabatt_codes_single`)));