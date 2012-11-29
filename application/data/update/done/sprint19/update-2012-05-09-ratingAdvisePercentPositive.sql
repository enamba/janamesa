-- @author Felix Haferkorn
-- @since 09.05.2012

alter table restaurants add column ratingAdvisePercentPositive int(11) default 0 after ratingDelivery;