-- @author vpriem

ALTER TABLE `upselling_goods` 
ADD `countCanton2626N` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `costCanton2626` ,
ADD `costCanton2626N` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `countCanton2626N`;