-- @author vpriem
-- @since 20.06.2012

ALTER TABLE `upselling_goods` 
    ADD `countCanton2626H` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `unitCanton2626S`,
    ADD `costCanton2626H` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `countCanton2626H`,
    ADD `unitCanton2626H` INT UNSIGNED NOT NULL DEFAULT 200 AFTER `costCanton2626H`;