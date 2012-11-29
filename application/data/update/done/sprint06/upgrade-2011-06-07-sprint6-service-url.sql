ALTER TABLE `restaurants` 
    ADD COLUMN `restUrl` VARCHAR(255) DEFAULT NULL UNIQUE,
    ADD COLUMN `caterUrl` VARCHAR(255) DEFAULT NULL UNIQUE,
    ADD COLUMN `greatUrl` VARCHAR(255) DEFAULT NULL UNIQUE;

UPDATE IGNORE `restaurants` r
SET r.restUrl = NULL,
    r.caterUrl = NULL,
    r.greatUrl = NULL;

UPDATE IGNORE `restaurants` r
INNER JOIN `city` c ON r.cityId = c.id
SET r.restUrl = base.nice_url(CONCAT('lieferservice-', r.name, '-', c.city)),
    r.caterUrl = base.nice_url(CONCAT('catering-', r.name, '-', c.city)),
    r.greatUrl = base.nice_url(CONCAT('grosshandel-', r.name, '-', c.city));

UPDATE IGNORE `restaurants` r
INNER JOIN `city` c ON r.cityId = c.id
SET r.restUrl = base.nice_url(CONCAT('lieferservice-', r.name, '-', c.city, '-', r.street))
WHERE r.restUrl IS NULL;

UPDATE IGNORE `restaurants` r
INNER JOIN `city` c ON r.cityId = c.id
SET r.caterUrl = base.nice_url(CONCAT('catering-', r.name, '-', c.city, '-', r.street))
WHERE r.caterUrl IS NULL;

UPDATE IGNORE `restaurants` r
INNER JOIN `city` c ON r.cityId = c.id
SET r.greatUrl = base.nice_url(CONCAT('grosshandel-', r.name, '-', c.city, '-', r.street))
WHERE r.greatUrl IS NULL;

-- ALTER TABLE `restaurants` DROP COLUMN `directLink`;