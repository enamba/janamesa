-- @author Toni Meuschke <meuschke@lieferando.de>
-- @since 15.03.2012
-- update table satellites with column 'premium' for Premium Satellites

ALTER TABLE `satellites` add COLUMN `premium` tinyint(4) DEFAULT 0;

-- avanti and charisma-grill
UPDATE `satellites` SET `premium` = 1 WHERE `restaurantId` IN (16631, 13747, 13748, 16259, 16260, 16261, 16262, 16263, 16264, 16265, 16266, 16267, 16268, 16269, 16270, 16271, 16272, 16274, 16275, 16276, 16277, 16278, 16279, 16280, 16281, 16283, 16284);