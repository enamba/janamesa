/**
 * Database upgrade v1.11
 *
 * @author vait
 */

ALTER TABLE `meals` CHANGE `min_amount` `minAmount` int(3) NULL DEFAULT 1;

ALTER TABLE meals ADD COLUMN `rank` int(6);
ALTER TABLE meal_sizes ADD COLUMN `rank` int(6);