
-- @author alex
-- @since 09.06.11

ALTER TABLE `meals` 
    ADD COLUMN `spicy` TINYINT(4) DEFAULT 0,
    ADD COLUMN `garlic` TINYINT(4) DEFAULT 0;

