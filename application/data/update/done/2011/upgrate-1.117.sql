/**
 * @author vpriem
 * @since 24.02.2011
 */
ALTER TABLE `courier_plz` ADD `description` VARCHAR(255) NOT NULL AFTER `mincost`;

UPDATE `courier_plz`
SET `description` = 'Zone I'
WHERE `courierId` = 4
    AND `plz` IN (60313, 60329, 60311);

UPDATE `courier_plz`
SET `description` = 'Zone II'
WHERE `courierId` = 4
    AND `plz` IN (60323, 60322, 60318, 60316, 60325, 60596, 60594, 60314, 60308, 60327);

UPDATE `courier_plz`
SET `description` = 'Zone III'
WHERE `courierId` = 4
    AND `plz` IN (60599, 60598, 60326, 60486, 60487, 60489, 60488, 60431, 60320, 60433, 60435, 60389, 60385, 60386, 60367);