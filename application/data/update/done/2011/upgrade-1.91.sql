/**
*   Database upgrade v1.91
*   @author alex
*   @since 05.01.2011
*/

alter table restaurants add column `demandssite`  TINYINT(4) default 0;
alter table restaurants add column `onlycash`     TINYINT(4) default 0;

alter table billing_assets add column `couriermwst`  INT(11) NOT NULL default 7 AFTER `mwst`;
alter table billing_assets add column `couriertotal` INT UNSIGNED default 0 AFTER `mwst`;
