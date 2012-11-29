
-- @author alex
-- @since 26.05.11

ALTER TABLE `meals`
    ADD COLUMN `hasPicture` TINYINT(4) DEFAULT 0,
    DROP COLUMN `picture`;

