-- @author vpriem
ALTER TABLE `restaurant_gprs_printer` 
    ADD `brand` VARCHAR(255) NOT NULL AFTER `gprsPrinterId`;