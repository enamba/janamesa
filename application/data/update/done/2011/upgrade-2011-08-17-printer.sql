-- @author vpriem
ALTER TABLE `printer_topup`
    ADD `firmware` VARCHAR(10) NOT NULL AFTER `simNumber`;
