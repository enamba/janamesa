/**
* Database upgrade v1.70
* @author alex
* @since 15.11.2010
*/

/*
* Kurier einem Rechnungsposten zuordnen
*/
ALTER TABLE `billing_assets` ADD COLUMN `courierId` INT UNSIGNED after `restaurantId`;

/*
* Die Zeiten, wann die Speisen aus dieser Kategorie bestellt werden können
*/
ALTER TABLE `meal_categories`
    ADD COLUMN `from` TIME DEFAULT '00:00:00' COMMENT 'Zeit ab wann man die Speise aus dieser Kategorie bestellen kann',
    ADD COLUMN `to` TIME DEFAULT '00:00:00' COMMENT 'Zeit bis wann man die Speise aus dieser Kategorie bestellen kann';

UPDATE `meal_categories` SET `to`='24:00:00';

ALTER TABLE `meals`
    DROP COLUMN `from`,
    DROP COLUMN `to`;
