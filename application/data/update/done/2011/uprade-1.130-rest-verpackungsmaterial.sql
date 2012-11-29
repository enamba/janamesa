
-- @author Felix Haferkorn <haferkorn@lieferando.de>
-- @since 06.04.2011
-- additional fields for restaurant

ALTER TABLE restaurants ADD pizzabox TINYINT(4) NULL DEFAULT 0 AFTER investprintingcostinfo;
ALTER TABLE restaurants ADD serviette TINYINT(4) NULL DEFAULT 0 AFTER pizzabox;
ALTER TABLE restaurants ADD bag TINYINT(4) NULL DEFAULT 0 AFTER serviette;

ALTER TABLE `geocoding` CHANGE `address` `address` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL;
