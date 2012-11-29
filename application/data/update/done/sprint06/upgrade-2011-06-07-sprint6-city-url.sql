ALTER TABLE `city` 
    ADD COLUMN `greatUrl` VARCHAR(255) DEFAULT NULL UNIQUE AFTER `parentCityId`,
    ADD COLUMN `caterUrl` VARCHAR(255) DEFAULT NULL UNIQUE AFTER `parentCityId`,
    ADD COLUMN `restUrl` VARCHAR(255) DEFAULT NULL UNIQUE AFTER `parentCityId`;

UPDATE IGNORE `city` 
    SET `restUrl` = base.nice_url(CONCAT('lieferservice-', city, '-', plz)),
    `caterUrl` = base.nice_url(CONCAT('catering-', city, '-', plz)),
    `greatUrl` = base.nice_url(CONCAT('grosshandel-', city, '-', plz));

UPDATE IGNORE `city` c 
    INNER JOIN `city` cc ON c.parentCityId = cc.id
    SET c.restUrl = base.nice_url(CONCAT('lieferservice-', cc.city, '-', c.city, '-', c.plz)),
    c.caterUrl = base.nice_url(CONCAT('catering-', cc.city, '-', c.city, '-', c.plz)),
    c.greatUrl = base.nice_url(CONCAT('grosshandel-', cc.city, '-', c.city, '-', c.plz));

-- Taxiresto
-- UPDATE IGNORE city 
--    SET `restUrl` = nice_url(CONCAT('livraison-', city, '-', plz)),
--    `caterUrl` = nice_url(CONCAT('traiteur-', city, '-', plz)),
--    `greatUrl` = nice_url(CONCAT('grossiste-', city, '-', plz));

-- Smakuje
-- UPDATE IGNORE city 
--    SET `restUrl` = nice_url(CONCAT('restauracja-', city, '-', plz)),
--    `caterUrl` = nice_url(CONCAT('catering-', city, '-', plz)),
--    `greatUrl` = nice_url(CONCAT('hurtownia-', city, '-', plz));