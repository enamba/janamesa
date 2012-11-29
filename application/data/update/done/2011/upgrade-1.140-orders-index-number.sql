
-- @author Felix Haferkorn <haferkorn@lieferando.de>
-- @since 26.05.11

ALTER TABLE `orders`
    ADD INDEX `fkNr` (`nr`);

