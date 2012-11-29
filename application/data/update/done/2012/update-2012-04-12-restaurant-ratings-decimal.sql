-- @author vpriem
-- @since 12.04.12

ALTER TABLE `restaurants` 
    CHANGE `ratingQuality` `ratingQuality` DECIMAL(6, 2) NULL DEFAULT '3',
    CHANGE `ratingDelivery` `ratingDelivery` DECIMAL(6, 2) NULL DEFAULT '3';