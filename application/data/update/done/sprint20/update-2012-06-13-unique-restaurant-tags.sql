-- @author Andre Ponert
-- @since 14.06.2012
-- make columns unique

ALTER TABLE `restaurant_tags` ADD UNIQUE INDEX (restaurantId, tag);